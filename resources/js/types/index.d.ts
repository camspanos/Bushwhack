import { InertiaLinkProps } from '@inertiajs/vue3';
import type { LucideIcon } from 'lucide-vue-next';

export interface Auth {
    user: User;
    isPremium: boolean;
    canFilterByYear: boolean;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icon?: LucideIcon;
    isActive?: boolean;
}

export type AppPageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    sidebarOpen: boolean;
};

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    is_premium?: boolean;
    city?: string | null;
    state?: string | null;
    country_id?: number | null;
    metric?: boolean;
}

export type BreadcrumbItemType = BreadcrumbItem;

export interface FishingLogLocation {
    id: number;
    name: string;
}

export interface FishingLogFish {
    id: number;
    species: string;
}

export interface FishingLogFly {
    id: number;
    name: string;
}

export interface FishingLogRod {
    id: number;
    rod_name: string;
}

export interface FishingLogFriend {
    id: number;
    name: string;
}

export interface FishingLogWeather {
    id: number;
    temperature?: number;
    conditions?: string;
    wind_speed?: number;
    wind_direction?: string;
    barometric_pressure?: number;
    humidity?: number;
}

export interface FishingLogWaterCondition {
    id: number;
    water_temperature?: number;
    water_level?: string;
    water_clarity?: string;
    surface_condition?: string;
    current_speed?: string;
}

export interface FishingLog {
    id: number;
    date: string;
    time?: string;
    user_location_id?: number;
    user_fish_id?: number;
    max_weight?: number;
    quantity: number;
    max_size?: number;
    user_fly_id?: number;
    user_rod_id?: number;
    style?: string;
    moon_phase?: string;
    moon_altitude?: number;
    moon_position?: string;
    time_of_day?: string;
    notes?: string;
    location?: FishingLogLocation;
    fish?: FishingLogFish;
    fly?: FishingLogFly;
    rod?: FishingLogRod;
    friends?: FishingLogFriend[];
    weather?: FishingLogWeather;
    water_condition?: FishingLogWaterCondition;
}
