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
      <select class="font-size-select" title="字體大小" @change="setFontSize($event.target.value)">
        <option value="">字體大小</option>
        <option value="12px">12px</option>
        <option value="14px">14px</option>
        <option value="16px">16px</option>
        <option value="18px">18px</option>
        <option value="20px">20px</option>
        <option value="24px">24px</option>
        <option value="28px">28px</option>
        <option value="32px">32px</option>
        <option value="36px">36px</option>
      </select>
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
import { TextStyle } from '@tiptap/extension-text-style'
import { Extension } from '@tiptap/core'

// 自訂字體大小 Extension（基於 TextStyle）
const FontSize = Extension.create({
  name: 'fontSize',
  addGlobalAttributes() {
    return [
      {
        types: ['textStyle'],
        attributes: {
          fontSize: {
            default: null,
            parseHTML: element => element.style.fontSize || null,
            renderHTML: attributes => {
              if (!attributes.fontSize) return {}
              return { style: `font-size: ${attributes.fontSize}` }
            },
          },
        },
      },
    ]
  },
  addCommands() {
    return {
      setFontSize: fontSize => ({ chain }) => {
        return chain().setMark('textStyle', { fontSize }).run()
      },
      unsetFontSize: () => ({ chain }) => {
        return chain().setMark('textStyle', { fontSize: null }).removeEmptyTextStyle().run()
      },
    }
  },
})

const props = defineProps({ modelValue: { type: String, default: '' } })
const emit = defineEmits(['update:modelValue'])

const editor = useEditor({
  content: props.modelValue,
  extensions: [
    StarterKit,
    Underline,
    TextAlign.configure({ types: ['heading', 'paragraph'] }),
    TextStyle,
    FontSize,
  ],
  onUpdate: ({ editor }) => {
    emit('update:modelValue', editor.getHTML())
  },
})

const setFontSize = (size) => {
  if (!editor.value) return
  if (!size) {
    editor.value.chain().focus().unsetFontSize().run()
  } else {
    editor.value.chain().focus().setFontSize(size).run()
  }
}

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
.font-size-select {
  height: 28px;
  padding: 0 4px;
  background: transparent;
  border: 1px solid #e2e8f0;
  border-radius: 4px;
  cursor: pointer;
  font-size: 12px;
  color: #374151;
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
