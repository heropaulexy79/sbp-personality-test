import { type ClassValue, clsx } from "clsx";
import { twMerge } from "tailwind-merge";
import { ref, onUnmounted } from "vue";

export function cn(...inputs: ClassValue[]) {
    return twMerge(clsx(inputs));
}

/**
 * @tutorial https://www.jacobparis.com/content/file-image-thumbnails#avoiding-memory-leaks-with-useobjecturls
 */
export function useObjectURLs() {
    const mapRef = ref(new Map());

    onUnmounted(() => {
        for (const [, url] of mapRef.value) {
            URL.revokeObjectURL(url);
        }
        mapRef.value.clear();
    });

    function getFileUrl(file: File): string {
        if (!mapRef.value.has(file)) {
            const url = URL.createObjectURL(file);
            mapRef.value.set(file, url);
        }

        const url = mapRef.value.get(file);
        if (!url) {
            throw new Error("File URL not found");
        }

        return url;
    }

    return getFileUrl;
}
