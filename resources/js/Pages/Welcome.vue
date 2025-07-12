<script setup lang="ts">
import { Head, Link } from "@inertiajs/vue3";
import { cn } from "@/lib/utils";
import { buttonVariants } from "@/Components/ui/button";

defineProps<{
  canLogin?: boolean;
  canRegister?: boolean;
  laravelVersion: string;
  phpVersion: string;
}>();
</script>

<template>
  <!-- <Head title="Welcome" /> -->
  <img
    id="background"
    class="pointer-events-none absolute inset-0 h-screen w-full object-cover"
    src="/images/home-bg.jpeg"
  />
  <div class="relative min-h-screen">
    <header class="">
      <div
        class="container grid grid-cols-2 items-center gap-2 border-b border-black/5 py-4 lg:grid-cols-2"
      >
        <div>
          <a href="/">
            <img src="/images/logo.png" alt="logo" />
          </a>
        </div>

        <nav v-if="canLogin" class="flex flex-1 justify-end gap-4">
          <Link
            v-if="$page.props.auth.user"
            :href="route('dashboard')"
            :class="cn(buttonVariants({ class: '' }))"
          >
            Dashboard
          </Link>

          <template v-else>
            <Link
              :href="route('login')"
              :class="
                cn(
                  buttonVariants({
                    variant: 'outline',
                    class: 'border-primary bg-transparent',
                  }),
                )
              "
            >
              Log in
            </Link>

            <Link
              v-if="canRegister"
              :href="route('register')"
              :class="cn(buttonVariants({ class: '' }))"
            >
              Register
            </Link>
          </template>
        </nav>
      </div>
    </header>

    <main class="mt-6">
      <div class="mx-auto mt-40 max-w-2xl p-8 text-center">
        <h1 class="text-4xl font-bold text-[hsla(252,100%,14%,1)]">
          Learn a Course on Culture
        </h1>
        <p class="mt-2 text-lg text-gray-700">
          Lorem ipsum dolor sit amet consectetur. Odio sed varius consectetur id
          vel placerat pulvinar tempor.
        </p>
        <form
          action="/signup"
          class="bg-background ring-offset-background focus-within:ring-ring mx-auto mt-4 flex max-w-md gap-2 rounded-md px-3 py-2 focus-within:ring-2 focus-within:ring-offset-2 focus-within:outline-hidden"
        >
          <div class="flex-1">
            <input
              type="email"
              name="email"
              class="bg-background placeholder:text-muted-foreground flex h-10 w-full rounded-md border-0 px-0 py-2 text-sm outline-hidden file:border-0 file:bg-transparent file:text-sm file:font-medium focus-visible:ring-transparent focus-visible:outline-hidden disabled:cursor-not-allowed disabled:opacity-50"
              placeholder="Enter your work email"
            />
          </div>
          <button
            class="bg-primary text-primary-foreground ring-offset-background hover:bg-primary/90 focus-visible:ring-ring inline-flex h-10 items-center justify-center gap-2 rounded-md px-4 py-2 text-sm font-medium whitespace-nowrap transition-colors focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:outline-hidden disabled:pointer-events-none disabled:opacity-50"
          >
            Get Started
          </button>
        </form>
      </div>
    </main>

    <!-- <footer
                class="py-16 text-center text-sm text-black dark:text-white/70"
            >
                Laravel v{{ laravelVersion }} (PHP v{{ phpVersion }})
            </footer> -->
  </div>
</template>
