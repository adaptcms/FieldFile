<template>
  <div>
    <template v-if="!isMultiple">
      <a v-if="value" :href="value" target="_blank" rel="noopener">
        <svg class="inline-block w-4 h-4 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
        </svg>
        <span>{{ field.name }}</span>
      </a>
    </template>

    <template v-if="isMultiple">
      <div class="flex flex-col">
        <div v-for="(file, index) in files" :key="`file-${index}`" class="w-auto mb-2">
          <a :href="file" target="_blank" rel="noopener">
            <svg class="inline-block w-4 h-4 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
            </svg>
            <span>{{ field.name }} #{{ index }}</span>
          </a>
        </div>
      </div>
    </template>
  </div>
</template>

<script>
import { get } from 'lodash'

export default {
  props: [
    'value',
    'field',
    'model',
    'package',
    'action'
  ],

  computed: {
    isMultiple () {
      return (get(this.field, 'meta.mode', 'single') === 'multiple')
    },

    files () {
      if (this.isMultiple) {
        return JSON.parse(this.value)
      }
    }
  }
}
</script>
