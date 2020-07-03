<template>
  <div>
    <template v-if="!isMultiple">
      <a :href="value" target="_blank" rel="noopener">
        <span class="mr-1">
          <i class="fas fa-paperclip" />
        </span>
        <span>{{ field.name }}</span>
      </a>
    </template>

    <template v-if="isMultiple">
      <div class="flex flex-col">
        <div v-for="(file, index) in files" :key="`file-${index}`" class="w-auto mb-2">
          <a :href="file" target="_blank" rel="noopener">
            <span class="mr-1">
              <i class="fas fa-paperclip" />
            </span>
            <span>{{ field.name }} #{{ index }}</span>
          </a>
        </div>
      </div>
    </template>
  </div>
</template>

<script>
import get from 'lodash.get'

export default {
  props: [
    'value',
    'field',
    'model',
    'module',
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
