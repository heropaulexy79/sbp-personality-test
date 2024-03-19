<script lang="ts" setup>
import { ref } from "vue";
import ApplicationLogo from "@/Components/ApplicationLogo.vue";
import NavLink from "@/Components/NavLink.vue";
import ResponsiveNavLink from "@/Components/ResponsiveNavLink.vue";
import { Link } from "@inertiajs/vue3";
import { Avatar, AvatarFallback, AvatarImage } from "@/Components/ui/avatar";
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from "@/Components/ui/dropdown-menu";
import { Button } from "@/Components/ui/button";
import GlobalLayout from "./GlobalLayout.vue";

const showingNavigationDropdown = ref(false);
</script>

<template>
    <GlobalLayout>
        <div>
            <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
                <nav
                    class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700"
                >
                    <!-- Primary Navigation Menu -->
                    <div class="container">
                        <div class="flex justify-between h-16">
                            <div class="flex">
                                <!-- Logo -->
                                <div class="shrink-0 flex items-center">
                                    <Link :href="route('dashboard')">
                                        <ApplicationLogo
                                            class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200"
                                        />
                                    </Link>
                                </div>

                                <!-- Navigation Links -->
                                <div
                                    class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex"
                                >
                                    <NavLink
                                        :href="route('dashboard')"
                                        :active="route().current('dashboard')"
                                    >
                                        Dashboard
                                    </NavLink>

                                    <NavLink
                                        v-if="
                                            $page.props.auth.user.role ===
                                                'ADMIN' &&
                                            $page.props.auth.user
                                                .organisation_id
                                        "
                                        :href="
                                            route('course.index', {
                                                // organisation:
                                                //     $page.props.auth.user
                                                //         .organisation_id,
                                            })
                                        "
                                        :active="route().current('course.*')"
                                    >
                                        Courses
                                    </NavLink>
                                </div>
                            </div>

                            <div class="hidden sm:flex sm:items-center sm:ms-6">
                                <!-- Settings Dropdown -->
                                <div class="ms-3 relative">
                                    <DropdownMenu>
                                        <DropdownMenuTrigger as-child>
                                            <Button
                                                variant="ghost"
                                                class="hover:bg-transparent -mr-4"
                                            >
                                                <Avatar
                                                    class="border border-primary"
                                                >
                                                    <AvatarImage
                                                        :src="`https://unavatar.io/${$page.props.auth.user.email}?ttl=1d`"
                                                        :alt="
                                                            $page.props.auth
                                                                .user.name
                                                        "
                                                    />
                                                    <AvatarFallback>
                                                        {{
                                                            $page.props.auth
                                                                .user.name[0]
                                                        }}
                                                    </AvatarFallback>
                                                </Avatar>

                                                {{ $page.props.auth.user.name }}
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent
                                            class="w-48"
                                            align="end"
                                        >
                                            <DropdownMenuLabel>
                                                <!-- My Account -->
                                                <div
                                                    class="font-medium text-sm text-gray-800 dark:text-gray-200"
                                                >
                                                    {{
                                                        $page.props.auth.user
                                                            .name
                                                    }}
                                                </div>
                                                <div
                                                    class="font-medium text-xs text-gray-500 truncate"
                                                >
                                                    {{
                                                        $page.props.auth.user
                                                            .email
                                                    }}
                                                </div>
                                            </DropdownMenuLabel>
                                            <DropdownMenuSeparator />
                                            <DropdownMenuItem as-child>
                                                <Link
                                                    :href="
                                                        route('profile.edit')
                                                    "
                                                >
                                                    Profile
                                                </Link>
                                            </DropdownMenuItem>
                                            <DropdownMenuItem
                                                v-if="
                                                    $page.props.auth.user
                                                        .role === 'ADMIN'
                                                "
                                                as-child
                                            >
                                                <Link
                                                    :href="
                                                        route(
                                                            'organisation.edit'
                                                        )
                                                    "
                                                >
                                                    Organisation
                                                </Link>
                                            </DropdownMenuItem>
                                            <DropdownMenuItem
                                                v-if="
                                                    $page.props.auth.user
                                                        .role === 'ADMIN'
                                                "
                                            >
                                                Billing
                                            </DropdownMenuItem>
                                            <DropdownMenuItem as-child>
                                                <Link
                                                    :href="route('logout')"
                                                    method="post"
                                                    as="button"
                                                    class="w-full"
                                                >
                                                    Logout
                                                </Link>
                                            </DropdownMenuItem>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </div>
                            </div>

                            <!-- Hamburger -->
                            <div class="-me-2 flex items-center sm:hidden">
                                <button
                                    @click="
                                        showingNavigationDropdown =
                                            !showingNavigationDropdown
                                    "
                                    class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-900 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out"
                                >
                                    <svg
                                        class="h-6 w-6"
                                        stroke="currentColor"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            :class="{
                                                hidden: showingNavigationDropdown,
                                                'inline-flex':
                                                    !showingNavigationDropdown,
                                            }"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M4 6h16M4 12h16M4 18h16"
                                        />
                                        <path
                                            :class="{
                                                hidden: !showingNavigationDropdown,
                                                'inline-flex':
                                                    showingNavigationDropdown,
                                            }"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12"
                                        />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Responsive Navigation Menu -->
                    <div
                        :class="{
                            block: showingNavigationDropdown,
                            hidden: !showingNavigationDropdown,
                        }"
                        class="sm:hidden"
                    >
                        <div class="pt-2 pb-3 space-y-1">
                            <ResponsiveNavLink
                                :href="route('dashboard')"
                                :active="route().current('dashboard')"
                            >
                                Dashboard
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                :href="route('course.index')"
                                :active="route().current('course.*')"
                            >
                                Courses
                            </ResponsiveNavLink>
                        </div>

                        <!-- Responsive Settings Options -->
                        <div
                            class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600"
                        >
                            <div class="px-4">
                                <div
                                    class="font-medium text-base text-gray-800 dark:text-gray-200"
                                >
                                    {{ $page.props.auth.user.name }}
                                </div>
                                <div class="font-medium text-sm text-gray-500">
                                    {{ $page.props.auth.user.email }}
                                </div>
                            </div>

                            <div class="mt-3 space-y-1">
                                <ResponsiveNavLink
                                    :href="route('profile.edit')"
                                >
                                    Profile
                                </ResponsiveNavLink>
                                <ResponsiveNavLink
                                    :href="route('organisation.edit')"
                                    v-if="
                                        $page.props.auth.user.role === 'ADMIN'
                                    "
                                >
                                    Organisation
                                </ResponsiveNavLink>
                                <ResponsiveNavLink
                                    :href="route('profile.edit')"
                                    v-if="
                                        $page.props.auth.user.role === 'ADMIN'
                                    "
                                >
                                    Billing
                                </ResponsiveNavLink>
                                <ResponsiveNavLink
                                    :href="route('logout')"
                                    method="post"
                                    as="button"
                                >
                                    Log Out
                                </ResponsiveNavLink>
                            </div>
                        </div>
                    </div>
                </nav>

                <!-- Page Heading -->
                <header
                    class="bg-white dark:bg-gray-800 shadow"
                    v-if="$slots.header"
                >
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        <slot name="header" />
                    </div>
                </header>

                <!-- Page Content -->
                <main>
                    <slot />
                </main>
            </div>
        </div>
    </GlobalLayout>
</template>
