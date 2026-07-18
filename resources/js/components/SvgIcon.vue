<template>
  <svg
    :class="className"
    fill="none"
    stroke="currentColor"
    stroke-width="2"
    stroke-linecap="round"
    stroke-linejoin="round"
    viewBox="0 0 24 24"
    xmlns="http://www.w3.org/2000/svg"
    v-html="pathContent"
  ></svg>
</template>

<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
  name: string;
  class?: string;
}>();

const className = computed(() => props.class || 'h-4 w-4');

const pathContent = computed(() => {
  const n = props.name.replace('mdi:', '').replace('-outline', '');
  switch (n) {
    case 'text-short':
    case 'card-text':
      return `<path d="M4 6h16M4 12h10M4 18h16" />`;
    case 'text-long':
      return `<path d="M4 6h16M4 12h16M4 18h10" />`;
    case 'numeric':
    case 'order-numeric-ascending':
      return `<rect x="3" y="3" width="18" height="18" rx="2" ry="2" /><path d="M9 8h2v8M13 12h2v4" />`;
    case 'arrow-down-drop-circle':
    case 'chevron-down-box':
      return `<circle cx="12" cy="12" r="10" /><path d="M8 10l4 4 4-4" />`;
    case 'check-all':
      return `<path d="M18 6L7 17l-5-5M22 10l-7.5 7.5L13 16" />`;
    case 'radiobox-marked':
    case 'circle-slice-8':
      return `<circle cx="12" cy="12" r="10" /><circle cx="12" cy="12" r="4" fill="currentColor" />`;
    case 'checkbox-marked':
      return `<rect x="3" y="3" width="18" height="18" rx="2" ry="2" /><path d="M9 12l2 2 4-4" />`;
    case 'calendar':
    case 'calendar-range':
      return `<rect x="3" y="4" width="18" height="18" rx="2" ry="2" /><line x1="16" y1="2" x2="16" y2="6" /><line x1="8" y1="2" x2="8" y2="6" /><line x1="3" y1="10" x2="21" y2="10" />`;
    case 'file-upload':
    case 'cloud-upload':
      return `<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M17 8l-5-5-5 5M12 3v12" />`;
    case 'format-list-bulleted-type':
    case 'folder':
      return `<rect x="3" y="3" width="18" height="18" rx="2" /><line x1="3" y1="9" x2="21" y2="9" /><line x1="9" y1="21" x2="9" y2="9" />`;
    case 'cursor-move':
      return `<path d="M5 9l-3 3 3 3M9 5l3-3 3 3M15 19l-3 3-3-3M19 9l3 3-3 3M2 12h20M12 2v20" />`;
    case 'unfold-more-horizontal':
      return `<path d="M8 9l4-4 4 4M16 15l-4 4-4-4" />`;
    case 'unfold-less-horizontal':
      return `<path d="M8 13l4 4 4-4M16 11l-4-4-4 4" />`;
    case 'delete':
      return `<path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />`;
    case 'cog':
      return `<circle cx="12" cy="12" r="3" /><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z" />`;
    case 'eye':
      return `<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" /><circle cx="12" cy="12" r="3" />`;
    case 'eye-off':
      return `<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24M1 1l22 22" />`;
    case 'arrow-up':
      return `<line x1="12" y1="19" x2="12" y2="5" /><polyline points="5 12 12 5 19 12" />`;
    case 'arrow-down':
      return `<line x1="12" y1="5" x2="12" y2="19" /><polyline points="19 12 12 19 5 12" />`;
    case 'chevron-right':
      return `<polyline points="9 18 15 12 9 6" />`;
    case 'chevron-down':
      return `<polyline points="6 9 12 15 18 9" />`;
    case 'plus':
      return `<line x1="12" y1="5" x2="12" y2="19" /><line x1="5" y1="12" x2="19" y2="12" />`;
    case 'school':
      return `<path d="M22 10v6M2 10l10-5 10 5-10 5z" /><path d="M6 12v5c0 2 2 3 6 3s6-1 6-3v-5" />`;
    case 'clipboard-text':
      return `<path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2" /><rect x="8" y="2" width="8" height="4" rx="1" ry="1" /><line x1="9" y1="12" x2="15" y2="12" /><line x1="9" y1="16" x2="13" y2="16" />`;
    case 'group':
      return `<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" /><circle cx="9" cy="7" r="4" /><path d="M23 21v-2a4 4 0 0 0-3-3.87" /><path d="M16 3.13a4 4 0 0 1 0 7.75" />`;
    case 'refresh':
      return `<path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67" />`;
    default:
      // Plus fallback
      return `<line x1="12" y1="5" x2="12" y2="19" /><line x1="5" y1="12" x2="19" y2="12" />`;
  }
});
</script>
