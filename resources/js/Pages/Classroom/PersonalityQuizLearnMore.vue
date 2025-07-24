<script setup lang="ts">
import { Avatar, AvatarFallback, AvatarImage } from "@/Components/ui/avatar";
import { Badge } from "@/Components/ui/badge";
import { Button, buttonVariants } from "@/Components/ui/button";
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from "@/Components/ui/card";
import { Input } from "@/Components/ui/input";
import { Separator } from "@/Components/ui/separator";
import {
  ArrowRight,
  Gift,
  ArrowLeft,
  BookOpen,
  Check,
  Copy,
  ExternalLink,
  Facebook,
  FileText,
  Linkedin,
  Share2,
  Twitter,
} from "lucide-vue-next";

interface BlogPostData {
  title: string;
  authorName: string;
  image: string;
  pubDate: Date;
  description: string;
  authorImage: string;
}

const defaultPost = {
  title: "The Secret Behind The Quiz",
  authorName: "John Doe",
  image:
    "https://deifkwefumgah.cloudfront.net/shadcnblocks/block/placeholder-1.svg",
  pubDate: new Date(),
  description:
    "A step-by-step guide to building a modern, responsive blog using React and Tailwind CSS.",
  authorImage:
    "https://deifkwefumgah.cloudfront.net/shadcnblocks/block/avatar-2.webp",
};

const { title, authorName, image, pubDate, description, authorImage } =
  defaultPost;

const handleShare = (platform: string) => {
  const url = window.location.href;
  let shareUrl = "";

  switch (platform) {
    case "twitter":
      shareUrl = `https://x.com/intent/tweet?url=${encodeURIComponent(url)}`;
      break;
    case "facebook":
      shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`;
      break;
    case "linkedin":
      shareUrl = `https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(url)}`;
      break;
  }

  if (shareUrl) {
    window.open(shareUrl, "_blank", "width=600,height=400");
  }
};
</script>

<template>
  <section class="py-32">
    <div class="container mt-10">
      <div class="relative gap-24">
        <div class="">
          <div class="mx-auto mb-10 flex max-w-5xl flex-col gap-4 text-center">
            <h1
              class="mx-auto max-w-3xl text-5xl font-semibold text-pretty md:text-5xl"
            >
              {{ title }}
            </h1>
            <!-- <h3
              class="text-muted-foreground mx-auto max-w-3xl text-lg md:text-xl"
            >
              {{ description }}
            </h3> -->
            <div
              class="flex items-center justify-center gap-3 text-sm md:text-base"
            >
              <Avatar class="h-8 w-8 border">
                <AvatarImage src="{authorImage}" />
                <AvatarFallback>{authorName.charAt(0)}</AvatarFallback>
              </Avatar>
            </div>
          </div>

          <!-- <img
            :src="image"
            alt="placeholder"
            class="mt-4 mb-8 aspect-video w-full rounded-lg border object-cover"
          /> -->
          <div class="prose dark:prose-invert mx-auto">
            <h2 class="">The Great Joke Tax</h2>
            <p class="">
              In a kingdom far away, where laughter once flowed freely, a
              peculiar tale unfolded about a king who decided to tax the very
              essence of joy itself - jokes and jest.
            </p>

            <h2>How the Tax System Works</h2>
            <p>
              The king, seeing how much happier his subjects were, realized the
              error of his ways and repealed the joke tax. Jokester was declared
              a hero, and the kingdom lived happily ever after.
            </p>

            <h2>The People&apos;s Rebellion</h2>
            <p>
              The people of the kingdom, feeling uplifted by the laughter,
              started to tell jokes and puns again, and soon the entire kingdom
              was in on the joke.
            </p>

            <p>
              The king, seeing how much happier his subjects were, realized the
              error of his ways and repealed the joke tax. Jokester was declared
              a hero, and the kingdom lived happily ever after.
            </p>

            <h2>The King&apos;s Plan</h2>

            <img
              src="https://deifkwefumgah.cloudfront.net/shadcnblocks/block/placeholder-1.svg"
              alt="placeholder"
              class="my-8 aspect-video w-full rounded-md object-cover"
            />
            <p>
              The king thought long and hard, and finally came up with
              <a href="#">a brilliant plan</a>: he would tax the jokes in the
              kingdom.
            </p>
            <blockquote>
              &ldquo;After all,&rdquo; he said, &ldquo;everyone enjoys a good
              joke, so it&apos;s only fair that they should pay for the
              privilege.&rdquo;
            </blockquote>
            <p>
              The king&apos;s subjects were not amused. They grumbled and
              complained, but the king was firm:
            </p>
            <ul>
              <li>1st level of puns: 5 gold coins</li>
              <li>2nd level of jokes: 10 gold coins</li>
              <li>3rd level of one-liners : 20 gold coins</li>
            </ul>
            <p>
              As a result, people stopped telling jokes, and the kingdom fell
              into a gloom. But there was one person who refused to let the
              king&apos;s foolishness get him down: a court jester named
              Jokester.
            </p>
            <div class="not-prose mt-10 flex items-center justify-between">
              <p class="text-sm font-medium">Share this article</p>
              <ul class="flex gap-2">
                <li>
                  <Button @click="() => handleShare('twitter')" size="icon">
                    <!-- class="hover:bg-muted inline-flex rounded-full border p-2 transition-colors" -->
                    <img
                      height="16"
                      width="16"
                      src="https://cdn.simpleicons.org/x/fff"
                    />
                  </Button>
                </li>
                <li>
                  <Button @click="() => handleShare('facebook')" size="icon">
                    <img
                      height="16"
                      width="16"
                      src="https://cdn.simpleicons.org/facebook/fff"
                    />
                  </Button>
                </li>
                <li>
                  <Button @click="() => handleShare('linkedin')" size="icon">
                    <Linkedin :size="16" />
                  </Button>
                </li>
                <!-- <li>
                <Button @click="() => handleShare('instagram')" size="icon">
                  <img
                    height="16"
                    width="16"
                    src="https://cdn.simpleicons.org/instagram/000"
                  />
                </Button>
              </li> -->
              </ul>
            </div>
          </div>
        </div>

        <!--  -->
        <div class="space-y-6 pt-24">
          <div>
            <h2
              class="mx-auto mb-10 max-w-3xl text-center text-4xl font-semibold text-pretty capitalize md:text-4xl"
            >
              Meet the Team Behind it All
            </h2>
          </div>
          <div class="mx-auto grid max-w-5xl grid-cols-2 gap-10">
            <Card>
              <CardHeader>
                <CardTitle class="flex items-center justify-center gap-2">
                  <img
                    src="https://www.therepublicomgroup.com/static/media/logo.e9c73038.png"
                    alt="placeholder"
                    class="mx-auto h-10 object-cover"
                  />
                </CardTitle>
              </CardHeader>
              <Separator />
              <CardContent>
                <CardDescription class="h-[100px] overflow-ellipsis">
                  As a group, we aim to foster creative transformation, advocate
                  for African representation in global marketing, deliver impact
                  at scale and build unique experiences through thought
                  leadership, innovation and a culturally attuned workforce.
                </CardDescription>
                <div class="mt-4 space-y-4">
                  <a
                    href="https://www.therepublicomgroup.com/"
                    :class="buttonVariants({ class: 'w-full' })"
                  >
                    Learn More
                  </a>
                </div>
              </CardContent>
            </Card>

            <Card>
              <CardHeader>
                <CardTitle class="flex items-center justify-center gap-2 pb-3">
                  <img
                    src="https://imageandtime.com/images/I&T%20-%20Logo-work.svg"
                    alt="placeholder"
                    class="mx-auto h-7 object-cover"
                  />
                </CardTitle>
              </CardHeader>
              <Separator />
              <CardContent>
                <CardDescription class="h-[100px] overflow-ellipsis">
                  We help ambitious brands craft remarkable identities, create
                  effective advertising and conceive memorable experiences that
                  connect people to their products and services.
                </CardDescription>
                <div class="mt-4 space-y-4">
                  <a
                    href="https://www.therepublicomgroup.com/"
                    :class="buttonVariants({ class: 'w-full' })"
                  >
                    Learn More
                  </a>
                </div>
              </CardContent>
            </Card>
          </div>
        </div>
      </div>

      <section class="mt-10 py-32">
        <div class="container">
          <div class="flex flex-col items-start md:items-center">
            <Badge>
              <Gift :size="16" />
            </Badge>
            <h4
              class="mt-4 text-3xl font-semibold tracking-tight md:text-center md:text-4xl xl:text-4xl"
            >
              Win a Prize!
            </h4>
            <p
              class="text-muted-foreground mt-2 text-lg font-medium md:text-center xl:text-xl"
            >
              Claim your prize, you've earned it!
            </p>
            <form
              class="mt-5 flex w-full flex-col gap-2 md:w-auto xl:mt-8 xl:gap-3"
            >
              <div
                class="group relative flex w-full items-center gap-2 rounded-lg px-3 md:w-[416px]"
              >
                <Input
                  type="email"
                  required
                  placeholder="Enter your email..."
                />
                <Button type="submit" aria-label="Submit form">
                  <ArrowRight />
                </Button>
              </div>
            </form>
          </div>
        </div>
      </section>
    </div>
  </section>
</template>
