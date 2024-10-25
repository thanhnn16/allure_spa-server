<template>
  <div class="carousel">
    <div class="carousel-inner" :style="{ transform: `translateX(-${currentIndex * 100}%)` }">
      <div v-for="(item, index) in items" :key="index" class="carousel-item">
        <img :src="item.file_path" :alt="'Product image ' + (index + 1)" class="w-full h-auto rounded-lg shadow-lg">
      </div>
    </div>
    <button @click="prev" class="carousel-control left">&lt;</button>
    <button @click="next" class="carousel-control right">&gt;</button>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
  items: Array,
})

const currentIndex = ref(0)

const next = () => {
  currentIndex.value = (currentIndex.value + 1) % props.items.length
}

const prev = () => {
  currentIndex.value = (currentIndex.value - 1 + props.items.length) % props.items.length
}
</script>

<style scoped>
.carousel {
  position: relative;
  overflow: hidden;
}

.carousel-inner {
  display: flex;
  transition: transform 0.3s ease-in-out;
}

.carousel-item {
  flex: 0 0 100%;
}

.carousel-control {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  background: rgba(0, 0, 0, 0.5);
  color: white;
  padding: 10px;
  border: none;
  cursor: pointer;
}

.carousel-control.left {
  left: 10px;
}

.carousel-control.right {
  right: 10px;
}
</style>
