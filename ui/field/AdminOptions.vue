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
    </div>
  </div>
</template>

<script>
import { isEmpty } from 'lodash'

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

  data () {
    return {
      selected: {
        mode: 'single'
      }
    }
  },

  mounted () {
    if (!isEmpty(this.value)) {
      this.selected = this.value
    } else {
      this.$emit('input', this.selected)
    }
  }
}
</script>
