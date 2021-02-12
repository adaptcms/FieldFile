<template>
  <div class="flex-col">
    <div class="w-full">
      <label class="text-lg font-normal text-gray-700 w-auto">
        Mode
      </label>

      <div class="flex mt-2">
        <div class="w-auto mr-3">
          <input
            type="radio"
            class="text-base py-3 px-3 shadow-sm inline-block"
            v-model="selected.mode"
            value="single"
          />

          <label>Single File</label>
        </div>

        <div class="w-auto">
          <input
            type="radio"
            class="text-base py-3 px-3 shadow-sm inline-block"
            v-model="selected.mode"
            value="multiple"
          />

          <label>Multiple Files</label>
        </div>
      </div>

      <template v-if="errorsList.length">
        <span v-for="error in errorsList" :key="error" class="border-red-700 block px-2 py-2 text-sm text-red-100 bg-red-500">
          {{ error }}
        </span>
      </template>
    </div>
  </div>
</template>

<script>
import { get, isEmpty } from 'lodash'

export default {
  props: [
    'value',
    'field',
    'package',
    'errors',
    'fields'
  ],

  watch: {
    value (newVal, oldVal) {
      if (newVal !== oldVal) {
        this.selected = newVal
      }
    },

    selected: {
      handler: function (newVal, oldVal) {
        this.$emit('input', newVal)
      },
      deep: true
    }
  },

  computed: {
    hasError () {
      let key = 'meta.mode'
      let errors = get(this.$page, 'props.errors')

      return (typeof errors[key] !== 'undefined')
    },

    errorsList () {
      let key = 'meta.mode'
      let errors = get(this.$page, 'props.errors')

      return (typeof errors[key] !== 'undefined' ? errors[key] : [])
    }
  },

  data () {
    return {
      selected: {
        mode: null
      }
    }
  },

  mounted () {
    if (!isEmpty(this.value)) {
      this.selected = this.value
    }
  }
}
</script>
