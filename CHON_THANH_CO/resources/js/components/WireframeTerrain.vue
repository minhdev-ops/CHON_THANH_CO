<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import * as THREE from 'three'

const canvasContainer = ref<HTMLDivElement>()

let scene: THREE.Scene
let camera: THREE.PerspectiveCamera
let renderer: THREE.WebGLRenderer
let terrain: THREE.Mesh
let terrainGlow: THREE.Mesh
let animationId: number
let mouse = { x: 0, y: 0, tx: 0, ty: 0 }
let time = 0

const SEGMENTS_W = 100
const SEGMENTS_H = 50
const TERRAIN_WIDTH = 28
const TERRAIN_HEIGHT = 14

function init() {
  if (!canvasContainer.value) return

  const w = canvasContainer.value.clientWidth
  const h = canvasContainer.value.clientHeight

  scene = new THREE.Scene()
  scene.fog = new THREE.FogExp2(0x2a2420, 0.035)

  camera = new THREE.PerspectiveCamera(45, w / h, 0.1, 100)
  camera.position.set(0, 6.5, 9)
  camera.lookAt(0, 3.5, 0)

  renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true })
  renderer.setSize(w, h)
  renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2))
  renderer.setClearColor(0x000000, 0)
  canvasContainer.value.appendChild(renderer.domElement)

  // Main wireframe terrain
  const geometry = new THREE.PlaneGeometry(TERRAIN_WIDTH, TERRAIN_HEIGHT, SEGMENTS_W, SEGMENTS_H)
  geometry.rotateX(-Math.PI / 2)

  const material = new THREE.MeshBasicMaterial({
    color: 0xb89b88,
    wireframe: true,
    transparent: true,
    opacity: 0.28,
  })

  terrain = new THREE.Mesh(geometry, material)
  scene.add(terrain)

  // Glow wireframe layer (slightly larger, more transparent)
  const glowGeometry = new THREE.PlaneGeometry(TERRAIN_WIDTH, TERRAIN_HEIGHT, SEGMENTS_W, SEGMENTS_H)
  glowGeometry.rotateX(-Math.PI / 2)

  const glowMaterial = new THREE.MeshBasicMaterial({
    color: 0xcdb9ad,
    wireframe: true,
    transparent: true,
    opacity: 0.12,
  })

  terrainGlow = new THREE.Mesh(glowGeometry, glowMaterial)
  terrainGlow.position.y = 0.05
  scene.add(terrainGlow)

  // Horizontal scan lines for depth
  const linesMaterial = new THREE.LineBasicMaterial({
    color: 0xb89b88,
    transparent: true,
    opacity: 0.15,
  })

  for (let z = -TERRAIN_HEIGHT / 2; z <= TERRAIN_HEIGHT / 2; z += TERRAIN_HEIGHT / 10) {
    const lineGeo = new THREE.BufferGeometry().setFromPoints([
      new THREE.Vector3(-TERRAIN_WIDTH / 2, 0, z),
      new THREE.Vector3(TERRAIN_WIDTH / 2, 0, z),
    ])
    const line = new THREE.Line(lineGeo, linesMaterial)
    scene.add(line)
  }

  window.addEventListener('mousemove', onMouseMove, { passive: true })
  window.addEventListener('resize', onResize, { passive: true })
}

function onMouseMove(e: MouseEvent) {
  mouse.tx = (e.clientX / window.innerWidth) * 2 - 1
  mouse.ty = (e.clientY / window.innerHeight) * 2 - 1
}

function onResize() {
  if (!canvasContainer.value) return
  const w = canvasContainer.value.clientWidth
  const h = canvasContainer.value.clientHeight
  camera.aspect = w / h
  camera.updateProjectionMatrix()
  renderer.setSize(w, h)
}

function animate() {
  animationId = requestAnimationFrame(animate)
  time += 0.006

  // Smooth mouse follow
  mouse.x += (mouse.tx - mouse.x) * 0.04
  mouse.y += (mouse.ty - mouse.y) * 0.04

  // Animate main terrain
  const positions = terrain.geometry.attributes.position
  const arr = positions.array as Float32Array

  for (let i = 0; i < positions.count; i++) {
    const x = arr[i * 3]
    const z = arr[i * 3 + 2]

    // Multi-octave waves for organic feel
    const wave1 = Math.sin(x * 0.35 + time * 1.1) * 0.4
    const wave2 = Math.cos(z * 0.45 + time * 0.8) * 0.3
    const wave3 = Math.sin((x + z) * 0.25 + time * 1.4) * 0.2
    const wave4 = Math.cos(x * 0.15 - time * 0.6) * Math.sin(z * 0.25 + time * 0.9) * 0.18
    const wave5 = Math.sin((x - z) * 0.2 + time * 0.5) * 0.12

    // Radial edge fade
    const edgeFadeX = 1 - Math.pow(Math.abs(x) / (TERRAIN_WIDTH / 2), 4)
    const edgeFadeZ = 1 - Math.pow(Math.abs(z) / (TERRAIN_HEIGHT / 2), 4)
    const edgeFade = Math.max(0, edgeFadeX * edgeFadeZ)

    arr[i * 3 + 1] = (wave1 + wave2 + wave3 + wave4 + wave5) * edgeFade
  }
  positions.needsUpdate = true

  // Sync glow layer with slight offset
  const glowPositions = terrainGlow.geometry.attributes.position
  const gArr = glowPositions.array as Float32Array
  for (let i = 0; i < glowPositions.count; i++) {
    gArr[i * 3 + 1] = arr[i * 3 + 1] * 0.7 + 0.1
  }
  glowPositions.needsUpdate = true

  // Camera follows mouse smoothly
  camera.position.x = mouse.x * 1.5
  camera.position.y = 6.5 + mouse.y * -0.6
  camera.lookAt(0, 3.5, 0)

  renderer.render(scene, camera)
}

onMounted(() => {
  init()
  animate()
})

onUnmounted(() => {
  cancelAnimationFrame(animationId)
  window.removeEventListener('mousemove', onMouseMove)
  window.removeEventListener('resize', onResize)
  renderer?.dispose()
  terrain?.geometry.dispose()
  terrainGlow?.geometry.dispose()
  if (canvasContainer.value && renderer.domElement.parentNode === canvasContainer.value) {
    canvasContainer.value.removeChild(renderer.domElement)
  }
})
</script>

<template>
  <div ref="canvasContainer" class="wireframe-terrain"></div>
</template>

<style scoped>
.wireframe-terrain {
  position: absolute;
  inset: 0;
  z-index: 0;
  background: linear-gradient(135deg, #4A403B 0%, #6B4F3A 25%, #5A4035 50%, #3A302B 75%, #2A2420 100%);
}
.wireframe-terrain :deep(canvas) {
  display: block;
  width: 100% !important;
  height: 100% !important;
}
</style>
