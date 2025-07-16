export function getLocalStorageItemsByPrefix(prefix: string) {
  const items: Record<string, any> = {};
  for (let i = 0; i < localStorage.length; i++) {
    const key = localStorage.key(i) as string;
    if (key.startsWith(prefix)) {
      try {
        items[key] = localStorage.getItem(key);
      } catch (e) {
        console.warn(`Could not retrieve item for key: ${key}`, e);
      }
    }
  }
  return items;
}
