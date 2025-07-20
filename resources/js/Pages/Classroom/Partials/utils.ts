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

export function extractParenthesizedText(inputString: string): string[] {
  const regex = /\(([^)]+)\)/g;
  const matches: string[] = [];
  let match;

  while ((match = regex.exec(inputString)) !== null) {
    matches.push(match[1]);
  }
  return matches;
}

export function removeParenthesizedText(inputString: string): string {
  const regex = /\([^)]*\)/g; // Matches parentheses and anything inside them
  return inputString.replace(regex, "").trim(); // Remove and trim any leading/trailing whitespace
}
