<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { toUrl } from '@/lib/utils';
import { dashboard, fishingLog, locationsPage, rodsPage, fishPage, fliesPage, friendsPage, badgesPage, leaderboard, following, about, home } from '@/routes';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { Info, LayoutGrid, Fish, MapPin, Wrench, Bug, Users, UserPlus, Trophy, Award } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        emoji: '🎛️',
    },
    {
        title: 'Fishing Log',
        href: fishingLog(),
        emoji: '📓',
    },
    {
        title: 'Locations',
        href: locationsPage(),
        emoji: '🌎',
    },
    {
        title: 'Rods',
        href: rodsPage(),
        emoji: '🎣',
    },
    {
        title: 'Fish',
        href: fishPage(),
        emoji: '🐟',
    },
    {
        title: 'Flies',
        href: fliesPage(),
        emoji: '🪝',
    },
    {
        title: 'Friends',
        href: friendsPage(),
        icon: Users,
        iconClass: 'text-teal-600 dark:text-teal-400',
    },
    {
        title: 'Badges',
        href: badgesPage(),
        emoji: '🏅',
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Leaderboard',
        href: leaderboard(),
        emoji: '🏆',
    },
    {
        title: 'Following',
        href: following(),
        emoji: '👤',
    },
    {
        title: 'About Us',
        href: about(),
        emoji: 'ℹ️',
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="toUrl(home())">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
