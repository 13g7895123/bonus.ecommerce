<template>
  <div class="rich-editor-wrap">
    <!-- Toolbar -->
    <div class="rich-toolbar">
      <button type="button" @click="editor?.chain().focus().toggleBold().run()" :class="{ active: editor?.isActive('bold') }" title="粗體">B</button>
      <button type="button" @click="editor?.chain().focus().toggleItalic().run()" :class="{ active: editor?.isActive('italic') }" title="斜體"><em>I</em></button>
      <button type="button" @click="editor?.chain().focus().toggleUnderline().run()" :class="{ active: editor?.isActive('underline') }" title="底線"><u>U</u></button>
      <button type="button" @click="editor?.chain().focus().toggleBulletList().run()" :class="{ active: editor?.isActive('bulletList') }" title="無序清單">≡</button>
      <button type="button" @click="editor?.chain().focus().toggleOrderedList().run()" :class="{ active: editor?.isActive('orderedList') }" title="有序清單">1.</button>
      <button type="button" @click="editor?.chain().focus().setTextAlign('left').run()" :class="{ active: editor?.isActive({ textAlign: 'left' }) }" title="靠左">←</button>
      <button type="button" @click="editor?.chain().focus().setTextAlign('center').run()" :class="{ active: editor?.isActive({ textAlign: 'center' }) }" title="置中">≡</button>
      <button type="button" @click="editor?.chain().focus().setTextAlign('right').run()" :class="{ active: editor?.isActive({ textAlign: 'right' }) }" title="靠右">→</button>
      <button type="button" @click="clearContent" title="清空">✕</button>
    </div>
    <editor-content :editor="editor" class="rich-content" />
  </div>
</template>

<script setup>
import { watch, onMounted, onBeforeUnmount } from 'vue'
import { useEditor, EditorContent } from '@tiptap/vue-3'
import StarterKit from '@tiptap/starter-kit'
import Underline from '@tiptap/extension-underline'
import TextAlign from '@tiptap/extension-text-align'

const props = defineProps({ modelValue: { type: String, default: '' } })
const emit = defineEmits(['update:modelValue'])

const editor = useEditor({
  content: props.modelValue,
  extensions: [
    StarterKit,
    Underline,
    TextAlign.configure({ types: ['heading', 'paragraph'] }),
  ],
  onUpdate: ({ editor }) => {
    emit('update:modelValue', editor.getHTML())
  },
})

watch(() => props.modelValue, (val) => {
  if (editor.value && editor.value.getHTML() !== val) {
    editor.value.commands.setContent(val || '', false)
  }
})

const clearContent = () => {
  editor.value?.commands.clearContent(true)
}

onBeforeUnmount(() => {
  editor.value?.destroy()
})
</script>

<style scoped>
.rich-editor-wrap {
  border: 1px solid #e2e8f0;
  border-radius: 8px;
  overflow: hidden;
  background: #fff;
}
.rich-toolbar {
  display: flex;
  flex-wrap: wrap;
  gap: 2px;
  padding: 6px 8px;
  background: #f8fafc;
  border-bottom: 1px solid #e2e8f0;
}
.rich-toolbar button {
  min-width: 30px;
  height: 28px;
  padding: 0 6px;
  background: transparent;
  border: 1px solid transparent;
  border-radius: 4px;
  cursor: pointer;
  font-size: 13px;
  color: #374151;
  transition: all 0.15s;
}
.rich-toolbar button:hover {
  background: #e2e8f0;
}
.rich-toolbar button.active {
  background: #1e293b;
  color: #fff;
  border-color: #1e293b;
}
.rich-content {
  min-height: 120px;
  padding: 10px 12px;
  font-size: 14px;
  color: #1e293b;
  outline: none;
}
:deep(.ProseMirror) {
  min-height: 120px;
  outline: none;
  text-align: left;
}
:deep(.ProseMirror p) { margin-bottom: 0.5em; }
:deep(.ProseMirror ul) { padding-left: 1.5em; list-style: disc; }
:deep(.ProseMirror ol) { padding-left: 1.5em; list-style: decimal; }
</style>
