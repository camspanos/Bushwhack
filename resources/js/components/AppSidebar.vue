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
import { dashboard, fishingLog, locationsPage, rodsPage, fishPage, fliesPage, friendsPage, leaderboard, following, about, home } from '@/routes';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { Info, LayoutGrid, Fish, MapPin, Wrench, Bug, Users, UserPlus, Trophy } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Fishing Log',
        href: fishingLog(),
        icon: Fish,
    },
    {
        title: 'Locations',
        href: locationsPage(),
        icon: MapPin,
    },
    {
        title: 'Rods',
        href: rodsPage(),
        icon: Wrench,
    },
    {
        title: 'Fish',
        href: fishPage(),
        icon: Fish,
    },
    {
        title: 'Flies',
        href: fliesPage(),
        icon: Bug,
    },
    {
        title: 'Friends',
        href: friendsPage(),
        icon: Users,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Leaderboard',
        href: leaderboard(),
        icon: Trophy,
    },
    {
        title: 'Following',
        href: following(),
        icon: UserPlus,
    },
    {
        title: 'About Us',
        href: about(),
        icon: Info,
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
