import axios from 'axios';

export const apiBaseUrl = import.meta.env.VITE_API_BASE_URL ?? 'http://api.lvh.me:8080';

export const apiClient = axios.create({
  baseURL: apiBaseUrl,
  withCredentials: true,
  headers: { Accept: 'application/json' },
});

export async function ensureCsrfCookie(): Promise<void> {
  await apiClient.get('/sanctum/csrf-cookie');
}
