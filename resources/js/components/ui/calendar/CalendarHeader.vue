<script lang="ts" setup>
import type { CalendarHeaderProps } from "reka-ui"
import type { HTMLAttributes } from "vue"
import { reactiveOmit } from "@vueuse/core"
import { CalendarHeader, useForwardProps } from "reka-ui"
import { cn } from "@/lib/utils"

const props = defineProps<CalendarHeaderProps & { class?: HTMLAttributes["class"], inlineNav?: boolean }>()

const delegatedProps = reactiveOmit(props, "class", "inlineNav")

const forwardedProps = useForwardProps(delegatedProps)
</script>

<template>
  <CalendarHeader
    data-slot="calendar-header"
    :class="cn(
      'flex justify-center pt-1 items-center w-full',
      inlineNav ? '' : 'relative px-8',
      props.class
    )"
    v-bind="forwardedProps"
  >
    <slot />
  </CalendarHeader>
</template>
