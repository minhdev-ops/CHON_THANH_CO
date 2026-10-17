<script setup lang="ts">
import { computed, onMounted, onUnmounted, ref, watch } from 'vue'
import type { HomeStat } from '../types'

const props = withDefaults(
  defineProps<{
    stats: HomeStat[]
    loading?: boolean
  }>(),
  { loading: false },
)

type Kind = 'NUMERIC' | 'RAW'
type Tier = 'L1' | 'L2' | 'L3'
type Phase = 'idle' | 'running' | 'done'

interface Cell {
  key: string
  label: string
  base: string
  suffix: string
  final: string
  kind: Kind
  target: number
  tier: Tier
}

const viNumber = new Intl.NumberFormat('vi-VN')

const isNumericBase = (base: string) => /^\d+$/.test(base) || /^\d{1,3}([.,]\d{3})*$/.test(base)

const tierOf = (final: string): Tier => {
  const len = final.length
  if (len <= 5) return 'L1'
  if (len <= 8) return 'L2'
  return 'L3'
}

const normalize = (stat: HomeStat, index: number): Cell | null => {
  const raw = String(stat?.value ?? '').trim()
  const label = String(stat?.label ?? '').trim()
  if (!raw || !label) return null

  const trailing = raw.match(/^(.+?)(\+)$/)
  const base = trailing ? trailing[1] : raw
  const suffix = trailing ? '+' : ''

  let kind: Kind = 'RAW'
  let target = 0
  let displayBase = base
  if (isNumericBase(base)) {
    kind = 'NUMERIC'
    target = parseInt(base.replace(/[.,]/g, ''), 10)
    displayBase = viNumber.format(target)
  }

  const final = displayBase + suffix
  return {
    key: `${index}-${raw}`,
    label,
    base: displayBase,
    suffix,
    final,
    kind,
    target,
    tier: tierOf(final),
  }
}

const cells = computed<Cell[]>(() =>
  props.stats.map((s, i) => normalize(s, i)).filter((c): c is Cell => c !== null),
)

const tierClass = (tier: Tier) =>
  tier === 'L1'
    ? 'text-[32px] md:text-[44px]'
    : tier === 'L2'
      ? 'text-[28px] md:text-[36px]'
      : 'text-[22px] md:text-[28px]'

const suffixClass = (tier: Tier) => (tier === 'L1' ? 'text-[22px] md:text-[30px]' : '')

const phase = ref<Phase>('idle')
const animated = ref<Record<number, number>>({})

const displayBase = (index: number) => {
  const cell = cells.value[index]
  if (!cell) return ''
  if (cell.kind === 'RAW') return cell.base
  if (phase.value === 'running') {
    const v = animated.value[index]
    return v === undefined ? cell.base : viNumber.format(v)
  }
  return cell.base
}

const blockRef = ref<HTMLElement | null>(null)
const entered = ref(false)
let observer: IntersectionObserver | null = null
let rafId: number | null = null

const prefersReducedMotion = () =>
  typeof window !== 'undefined' && window.matchMedia('(prefers-reduced-motion: reduce)').matches

const startCountUp = () => {
  if (phase.value !== 'idle') return
  if (prefersReducedMotion()) {
    phase.value = 'done'
    return
  }
  const numeric = cells.value.some((c) => c.kind === 'NUMERIC')
  if (!numeric) {
    phase.value = 'done'
    return
  }
  cells.value.forEach((c, i) => {
    if (c.kind === 'NUMERIC') animated.value[i] = 0
  })
  phase.value = 'running'

  const duration = 2200
  const startTime = performance.now()
  const step = (now: number) => {
    const p = Math.min((now - startTime) / duration, 1)
    const eased = 1 - Math.pow(1 - p, 4)
    cells.value.forEach((c, i) => {
      if (c.kind === 'NUMERIC') animated.value[i] = Math.round(eased * c.target)
    })
    if (p < 1) {
      rafId = requestAnimationFrame(step)
    } else {
      phase.value = 'done'
      animated.value = {}
    }
  }
  rafId = requestAnimationFrame(step)
}

const tryStart = () => {
  if (entered.value && !props.loading) startCountUp()
}

watch(() => props.loading, tryStart)

onMounted(() => {
  observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting && !entered.value) {
          entered.value = true
          observer?.disconnect()
          tryStart()
        }
      })
    },
    { threshold: 0.3 },
  )
  if (blockRef.value) observer.observe(blockRef.value)
})

onUnmounted(() => {
  observer?.disconnect()
  if (rafId !== null) cancelAnimationFrame(rafId)
})
</script>

<template>
  <div v-if="cells.length" class="bg-surface-vlm rounded-[10px] p-5 md:p-6">
    <dl ref="blockRef" class="flex flex-col">
      <div
        v-for="(cell, i) in cells"
        :key="cell.key"
        class="home-stat-row relative flex items-end justify-between gap-4 md:gap-6 border-t border-border-primary py-3 reveal"
        :class="`reveal-delay-${Math.min(i + 1, 6)}`"
      >
        <dt
          class="flex-1 min-w-0 text-left text-[11px] md:text-[12px] font-bold uppercase tracking-[0.08em] text-[#4A403B] leading-[1.35]"
        >
          {{ cell.label }}
        </dt>
        <dd
          class="m-0 shrink-0 text-right min-h-[32px] md:min-h-[44px] flex items-end justify-end gap-1 whitespace-nowrap font-condensed font-black leading-none tabular-nums text-[#6B4F3A]"
          :class="tierClass(cell.tier)"
        >
          <span aria-hidden="true">{{ displayBase(i) }}</span>
          <span
            v-if="cell.suffix"
            aria-hidden="true"
            class="text-[#6B4F3A]"
            :class="suffixClass(cell.tier)"
            >{{ cell.suffix }}</span
          >
          <span class="sr-only">{{ cell.final }}</span>
        </dd>
      </div>
    </dl>
  </div>
</template>
