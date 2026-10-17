<script setup lang="ts">
import { computed } from 'vue'
import { parseRichText, inlineSegments, type RichBlock } from '../utils/richText'

const props = withDefaults(
    defineProps<{
        text?: string | null
        /** 'all' hiện toàn bộ, 'lead' chỉ đoạn dẫn, 'rest' bỏ đoạn dẫn. */
        mode?: 'all' | 'lead' | 'rest'
    }>(),
    { text: null, mode: 'all' },
)

/**
 * Đoạn dẫn là block paragraph đầu tiên. Nếu admin gõ "## ..." ngay dòng đầu
 * thì không có đoạn dẫn, mọi block hiển thị trong thẻ "Mô tả chi tiết".
 */
const blocks = computed<RichBlock[]>(() => {
    const parsed = parseRichText(props.text)
    if (props.mode === 'all') return parsed

    const first = parsed[0]
    const hasLead = first?.type === 'paragraph'
    if (props.mode === 'lead') return hasLead ? [first] : []
    return hasLead ? parsed.slice(1) : parsed
})
</script>

<template>
    <div v-if="blocks.length">
        <template v-for="(block, index) in blocks" :key="index">
            <h3
                v-if="block.type === 'heading' && block.level === 2"
                class="text-[22px] md:text-[24px] font-extrabold text-text-main tracking-tight mt-8 first:mt-0 mb-3"
            >
                <template v-for="(seg, i) in inlineSegments(block.text)" :key="i">
                    <strong v-if="seg.b" class="font-extrabold">{{ seg.t }}</strong>
                    <template v-else>{{ seg.t }}</template>
                </template>
            </h3>

            <h4
                v-else-if="block.type === 'heading'"
                class="text-[18px] font-bold text-text-main mt-6 first:mt-0 mb-2"
            >
                <template v-for="(seg, i) in inlineSegments(block.text)" :key="i">
                    <strong v-if="seg.b" class="font-extrabold">{{ seg.t }}</strong>
                    <template v-else>{{ seg.t }}</template>
                </template>
            </h4>

            <p v-else-if="block.type === 'paragraph'" class="text-text-secondary leading-[1.8] text-[16px] mb-4 last:mb-0">
                <template v-for="(seg, i) in inlineSegments(block.text)" :key="i">
                    <strong v-if="seg.b" class="font-bold text-text-main">{{ seg.t }}</strong>
                    <template v-else>{{ seg.t }}</template>
                </template>
            </p>

            <ol v-else-if="block.ordered" class="list-decimal pl-6 space-y-2 text-text-secondary leading-[1.8] text-[16px] mb-4 last:mb-0">
                <li v-for="(item, i) in block.items" :key="i">
                    <template v-for="(seg, si) in inlineSegments(item)" :key="si">
                        <strong v-if="seg.b" class="font-bold text-text-main">{{ seg.t }}</strong>
                        <template v-else>{{ seg.t }}</template>
                    </template>
                </li>
            </ol>

            <ul v-else class="list-disc pl-6 space-y-2 text-text-secondary leading-[1.8] text-[16px] mb-4 last:mb-0">
                <li v-for="(item, i) in block.items" :key="i">
                    <template v-for="(seg, si) in inlineSegments(item)" :key="si">
                        <strong v-if="seg.b" class="font-bold text-text-main">{{ seg.t }}</strong>
                        <template v-else>{{ seg.t }}</template>
                    </template>
                </li>
            </ul>
        </template>
    </div>
</template>