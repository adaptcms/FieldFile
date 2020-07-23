<template>
  <div class="flex flex-col items-center lg:flex-row lg:justify-between lg:items-start">
    <div class="w-auto">
      <label
        :id="`form-${field.column_name}`"
        class="w-64 flex flex-col items-center px-4 py-6 bg-white text-blue-500 rounded-lg shadow-lg tracking-wide uppercase border cursor-pointer hover:text-white"
        :class="{ 'border-red-500 hover:bg-red-500': errors[field.column_name].is, 'border-blue-500 hover:bg-blue-500': !errors[field.column_name].is }"
      >
        <i class="fas fa-lg fa-cloud-upload-alt" />

        <template v-if="isMultiple">
          <span class="mt-2 text-base leading-normal">Select files</span>
          <input type="file" class="hidden" ref="fileInput" @input="setUploadFiles" multiple />
        </template>

        <template v-if="!isMultiple">
          <span class="mt-2 text-base leading-normal">Select a file</span>
          <input type="file" class="hidden" ref="fileInput" @input="setUploadFiles" />
        </template>
      </label>
    </div>

    <div class="w-auto flex flex-col mt-4 text-right lg:justify-right lg:mt-0">
      <div v-for="(file, index) in files" :key="`file-${file.name}`" class="w-full mb-1">
        <span class="mr-1">{{ file.file_name || file.name }}</span>
        <span
          class="cursor-pointer inline-flex items-center px-4 py-2 bg-red-200 border border-red-300 text-sm leading-5 font-medium rounded-md text-red-500 bg-white hover:text-red-800 focus:outline-none focus:shadow-outline-red focus:border-red-300 active:text-red-900 active:bg-red-50 transition duration-150 ease-in-out opacity-75 hover:opacity-100"
          @click.prevent="removeFile(index, file)"
        >
          <i class="fas fa-trash" />
        </span>
      </div>
    </div>
  </div>
</template>

<script>
import { get, isEmpty, isEqual } from 'lodash'

export default {
  props: [
    'value',
    'field',
    'errors',
    'formMeta',
    'model',
    'action'
  ],

  watch: {
    selected (newVal, oldVal) {
      let selected = newVal.filter(row => typeof row.id === 'undefined')

      this.$emit('input', selected)
    },

    removeFiles (newVal, oldVal) {
      this.$emit('extra', {
        key: 'removeFiles',
        value: newVal
      })
    }
  },

  computed: {
    isMultiple () {
      return (get(this.field, 'meta.mode', 'single') === 'multiple')
    }
  },

  data () {
    return {
      selected: [],
      files: [],
      removeFiles: []
    }
  },

  methods: {
    setUploadFiles () {
      if (!this.isMultiple && this.files.length) {
        this.removeFile(0, this.files[0], false)
      }

      let files = this.$refs.fileInput.files
      for (let i = 0; i < files.length; i++) {
        let file = files[i]

        this.selected.push(file)
      }

      this.setFiles()
    },

    setFiles (files = null) {
      if (!files) {
        files = this.selected
      }

      if (!this.isMultiple) {
        this.files = [ files[0] ]
      } else {
        for (let i in files) {
          let file = files[i]
          let findIndex = this.files.findIndex(row => isEqual(row, file))

          if (findIndex === -1) {
            this.files.push(file)
          }
        }
      }
    },

    removeFile (index, file, shouldConfirm = true) {
      if (!shouldConfirm || confirm('Are you sure you want to delete this file?')) {
        this.files.splice(index, 1)

        if (typeof file.id === 'undefined') {
          if (this.isMultiple) {
            let activeIndex = this.selected.findIndex(row => row.name === file.name)

            if (activeIndex !== -1) {
              this.selected.splice(activeIndex, 1)
            }
          } else {
            this.selected = []
          }
        } else {
          let activeIndex = this.selected.findIndex(row => row.id === file.id)

          if (activeIndex !== -1) {
            this.selected.splice(activeIndex, 1)
          }

          this.removeFiles.push(file.id)
        }
      }
    }
  },

  mounted () {
    if (!isEmpty(this.value)) {
      let media = this.formMeta[this.field.column_name]

      if (media.length) {
        this.setFiles(media)
        this.selected = media
      }
    }
  }
}
</script>
