<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount } from 'vue'
import * as THREE from 'three'

const container = ref<HTMLDivElement>()
let renderer: THREE.WebGLRenderer | null = null
let scene: THREE.Scene | null = null
let camera: THREE.PerspectiveCamera | null = null
let animId: number | null = null
let time = 0
let mx = 0, my = 0
const mouseW = new THREE.Vector3(999, 999, 0)

const CONNECT_DIST = 2.0
const origPos: number[] = []
let vertCount = 0
let terrainGeo: THREE.PlaneGeometry | null = null
let terrainMat: THREE.ShaderMaterial | null = null
let terrainMesh: THREE.Mesh | null = null
let lineGeo: THREE.BufferGeometry | null = null
let lineMesh: THREE.LineSegments | null = null
let linePositions: THREE.BufferAttribute | null = null
let lineAlphas: THREE.BufferAttribute | null = null

const terrainVS = `
  uniform float uTime;
  varying vec2 vUv;
  varying float vElev;
  void main(){
    vUv = uv;
    vec3 p = position;
    float w1=sin(p.x*0.5+uTime*0.35)*1.0;
    float w2=cos(p.z*0.4+uTime*0.25)*0.75;
    float w3=sin(p.x*0.3+p.z*0.35+uTime*0.2)*0.55;
    float w4=cos(p.x*0.8-p.z*0.3+uTime*0.4)*0.35;
    float edge=smoothstep(-3.0,1.0,p.z)*smoothstep(10.0,5.0,p.z);
    float elev=(w1+w2+w3+w4)*edge;
    p.y+=elev;
    vElev=elev;
    gl_Position=projectionMatrix*modelViewMatrix*vec4(p,1.0);
  }
`

const terrainFS = `
  varying vec2 vUv;
  varying float vElev;
  void main(){
    float t=clamp((vElev+2.0)/4.5,0.0,1.0);
    vec3 c1=vec3(0.22,0.19,0.17);
    vec3 c2=vec3(0.45,0.38,0.32);
    vec3 c3=vec3(0.72,0.61,0.53);
    vec3 c4=vec3(0.88,0.80,0.72);
    vec3 col=mix(c1,c2,smoothstep(0.0,0.25,t));
    col=mix(col,c3,smoothstep(0.25,0.55,t));
    col=mix(col,c4,smoothstep(0.55,0.85,t));
    float edge=smoothstep(0.0,0.12,vUv.x)*smoothstep(1.0,0.88,vUv.x);
    float vf=smoothstep(0.0,0.08,vUv.y)*smoothstep(1.0,0.92,vUv.y);
    float a=edge*vf*0.5;
    gl_FragColor=vec4(col,a);
  }
`

function init(){
  if(!container.value) return
  scene = new THREE.Scene()
  scene.fog = new THREE.FogExp2(0x1a1614, 0.03)
  camera = new THREE.PerspectiveCamera(50, innerWidth/innerHeight, 0.1, 100)
  camera.position.set(0, 4, 8)
  camera.lookAt(0, -1, 2)

  renderer = new THREE.WebGLRenderer({alpha:true, antialias:true})
  renderer.setSize(innerWidth, innerHeight)
  renderer.setPixelRatio(Math.min(devicePixelRatio,2))
  renderer.setClearColor(0x000000,0)
  container.value.appendChild(renderer.domElement)

  // Terrain
  terrainGeo = new THREE.PlaneGeometry(20, 15, 59, 44)
  terrainGeo.rotateX(-Math.PI/2.2)
  terrainMat = new THREE.ShaderMaterial({
    vertexShader: terrainVS, fragmentShader: terrainFS,
    uniforms:{uTime:{value:0}},
    wireframe:true, transparent:true, side:THREE.DoubleSide, depthWrite:false,
  })
  terrainMesh = new THREE.Mesh(terrainGeo, terrainMat)
  scene.add(terrainMesh)

  // Store original positions
  const pa = terrainGeo.attributes.position
  vertCount = pa.count
  for(let i=0; i<vertCount; i++){
    origPos.push(pa.getX(i), pa.getY(i), pa.getZ(i))
  }

  // Lines - simple approach: 2 verts per line, position.x = 0 or 1
  const maxVerts = vertCount * 2
  const posArr = new Float32Array(maxVerts * 3)
  const alphaArr = new Float32Array(maxVerts)
  for(let i=0; i<vertCount; i++){
    posArr[i*6+0]=0; posArr[i*6+1]=0; posArr[i*6+2]=0
    posArr[i*6+3]=1; posArr[i*6+4]=0; posArr[i*6+5]=0
  }

  lineGeo = new THREE.BufferGeometry()
  lineGeo.setAttribute('position', new THREE.BufferAttribute(posArr, 3))
  lineGeo.setAttribute('aAlpha', new THREE.BufferAttribute(alphaArr, 1))
  lineGeo.setDrawRange(0, 0)

  lineMesh = new THREE.LineSegments(lineGeo, new THREE.ShaderMaterial({
    vertexShader: `
      attribute float aAlpha;
      attribute vec3 aStart;
      attribute vec3 aEnd;
      varying float vA;
      void main(){
        vA = aAlpha;
        vec3 p = mix(aStart, aEnd, position.x);
        gl_Position = projectionMatrix*modelViewMatrix*vec4(p,1.0);
      }
    `,
    fragmentShader: `
      varying float vA;
      void main(){
        gl_FragColor=vec4(0.72,0.61,0.53, vA*0.5);
      }
    `,
    transparent:true, depthWrite:false,
  }))
  lineMesh.frustumCulled = false
  scene.add(lineMesh)
}

function updateLines(){
  if(!lineGeo||!terrainGeo) return
  const pa = terrainGeo.attributes.position
  const arr = pa.array as Float32Array
  const posAttr = lineGeo.getAttribute('position') as THREE.BufferAttribute
  const alphaAttr = lineGeo.getAttribute('aAlpha') as THREE.BufferAttribute

  let n=0
  for(let i=0; i<vertCount; i++){
    const bx = arr[i*3]
    const by = arr[i*3+1]
    const bz = arr[i*3+2]
    const dx = bx-mouseW.x, dy = by-mouseW.y, dz = bz-mouseW.z
    const dist = Math.sqrt(dx*dx+dy*dy+dz*dz)
    if(dist < CONNECT_DIST){
      const a = 1 - dist/CONNECT_DIST
      const idx = n*6
      // Start point (terrain vertex)
      posAttr.array[idx]   = bx
      posAttr.array[idx+1] = by
      posAttr.array[idx+2] = bz
      // End point (mouse)
      posAttr.array[idx+3] = mouseW.x
      posAttr.array[idx+4] = mouseW.y
      posAttr.array[idx+5] = mouseW.z
      alphaAttr.array[n*2] = a
      alphaAttr.array[n*2+1] = a
      n++
    }
  }
  lineGeo.setDrawRange(0, n*2)
  posAttr.needsUpdate = true
  alphaAttr.needsUpdate = true
}

function tick(){
  animId=requestAnimationFrame(tick)
  if(!renderer||!scene||!camera||!terrainMat||!terrainGeo) return
  time+=0.016
  terrainMat.uniforms.uTime.value=time

  // Update terrain vertices
  const pa=terrainGeo.attributes.position
  const arr=pa.array as Float32Array
  for(let i=0;i<vertCount;i++){
    const ox=origPos[i*3], oz=origPos[i*3+2]
    const w1=Math.sin(ox*0.5+time*0.35)*1.0
    const w2=Math.cos(oz*0.4+time*0.25)*0.75
    const w3=Math.sin(ox*0.3+oz*0.35+time*0.2)*0.55
    const w4=Math.cos(ox*0.8-oz*0.3+time*0.4)*0.35
    const edge=Math.max(0,Math.min(1,(oz+3)/4))*Math.max(0,Math.min(1,(10-oz)/5))
    arr[i*3+1] = (w1+w2+w3+w4)*edge
  }
  pa.needsUpdate=true
  terrainGeo.computeBoundingSphere()

  updateLines()

  camera.position.x+=(mx*1.5-camera.position.x)*0.01
  camera.position.y+=(4+my*-0.5-camera.position.y)*0.01
  camera.lookAt(0,-1,2)
  renderer.render(scene,camera)
}

function onMove(e:MouseEvent){
  mx=(e.clientX/innerWidth)*2-1
  my=(e.clientY/innerHeight)*2+1
  // Project mouse to world Z=0 plane
  const v=new THREE.Vector3(mx,-my,0.5).unproject(camera!)
  const d=v.sub(camera!.position).normalize()
  const t=-camera!.position.z/d.z
  const pt=camera!.position.clone().add(d.multiplyScalar(t))
  mouseW.set(pt.x, pt.y, 0)
}

function onResize(){
  if(!camera||!renderer) return
  camera.aspect=innerWidth/innerHeight
  camera.updateProjectionMatrix()
  renderer.setSize(innerWidth,innerHeight)
}

onMounted(()=>{
  init(); tick()
  addEventListener('mousemove',onMove,{passive:true})
  addEventListener('resize',onResize,{passive:true})
})
onBeforeUnmount(()=>{
  cancelAnimationFrame(animId!)
  removeEventListener('mousemove',onMove)
  removeEventListener('resize',onResize)
  terrainGeo?.dispose(); terrainMat?.dispose()
  lineGeo?.dispose(); (lineMesh?.material as THREE.Material)?.dispose()
  renderer?.dispose()
})
</script>

<template>
  <div ref="container" class="absolute inset-0 z-[1]"></div>
</template>
