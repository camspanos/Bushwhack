<script setup lang="ts">
import {
    SidebarGroup,
    SidebarGroupContent,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { toUrl } from '@/lib/utils';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';

interface Props {
    items: NavItem[];
    class?: string;
}

defineProps<Props>();

const isExternalLink = (href: any): boolean => {
    if (typeof href === 'string') {
        return href.startsWith('http://') || href.startsWith('https://');
    }
    return false;
};
</script>

<template>
    <SidebarGroup
        :class="`group-data-[collapsible=icon]:p-0 ${$props.class || ''}`"
    >
        <SidebarGroupContent>
            <SidebarMenu>
                <SidebarMenuItem v-for="item in items" :key="item.title">
                    <SidebarMenuButton
                        class="text-neutral-600 hover:text-neutral-800 dark:text-neutral-300 dark:hover:text-neutral-100"
                        as-child
                    >
                        <!-- External Link -->
                        <a
                            v-if="isExternalLink(item.href)"
                            :href="toUrl(item.href)"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            <span v-if="item.emoji" class="text-base leading-none">{{ item.emoji }}</span>
                            <component v-else-if="item.icon" :is="item.icon" />
                            <span>{{ item.title }}</span>
                        </a>
                        <!-- Internal Link -->
                        <Link v-else :href="item.href">
                            <span v-if="item.emoji" class="text-base leading-none">{{ item.emoji }}</span>
                            <component v-else-if="item.icon" :is="item.icon" />
                            <span>{{ item.title }}</span>
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarGroupContent>
    </SidebarGroup>
</template>
