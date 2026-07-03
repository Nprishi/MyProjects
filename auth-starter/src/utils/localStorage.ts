export function setLocalStorage<T>(key: string, value: T): void {
  try {
    if (typeof window !== 'undefined') {
      window.localStorage.setItem(key, JSON.stringify(value));
    }
  } catch (error) {
    console.error(`Failed to set localStorage:`, error);
  }
}

export function getLocalStorage<T>(key: string): T | null {
  try {
    if (typeof window !== 'undefined') {
      const item = window.localStorage.getItem(key);
      return item ? JSON.parse(item) : null;
    }
  } catch (error) {
    console.error(`Failed to get localStorage:`, error);
  }
  return null;
}

export function removeLocalStorage(key: string): void {
  try {
    if (typeof window !== 'undefined') {
      window.localStorage.removeItem(key);
    }
  } catch (error) {
    console.error(`Failed to remove localStorage:`, error);
  }
}