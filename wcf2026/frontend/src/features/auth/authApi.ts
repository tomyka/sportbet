import { apiClient, ensureCsrfCookie } from '../../lib/api-client';

export type Me = {
  id: number;
  email: string;
  name: string;
  display_name: string | null;
  time_zone: string;
  locale: string;
  email_verified: boolean;
  is_global_admin: boolean;
};

export async function fetchMe(): Promise<Me> {
  const res = await apiClient.get<{ data: Me }>('/api/v1/auth/me');
  return res.data.data;
}

export async function login(email: string, password: string): Promise<Me> {
  await ensureCsrfCookie();
  const res = await apiClient.post<{ data: Me }>('/api/v1/auth/login', { email, password });
  return res.data.data;
}

export async function logout(): Promise<void> {
  await apiClient.post('/api/v1/auth/logout');
}

export async function register(data: {
  name: string;
  email: string;
  password: string;
  password_confirmation: string;
}): Promise<Me> {
  await ensureCsrfCookie();
  const res = await apiClient.post<{ data: Me }>('/api/v1/auth/register', data);
  return res.data.data;
}

export async function forgotPassword(email: string): Promise<void> {
  await apiClient.post('/api/v1/auth/password/forgot', { email });
}

export async function resetPassword(data: {
  token: string;
  email: string;
  password: string;
  password_confirmation: string;
}): Promise<void> {
  await apiClient.post('/api/v1/auth/password/reset', data);
}

export async function updateProfile(data: {
  name?: string;
  display_name?: string;
  time_zone?: string;
  locale?: string;
}): Promise<Me> {
  const res = await apiClient.patch<{ data: Me }>('/api/v1/auth/me', data);
  return res.data.data;
}

export async function changePassword(data: {
  current_password: string;
  password: string;
  password_confirmation: string;
}): Promise<void> {
  await apiClient.post('/api/v1/auth/password', data);
}

export async function resendVerificationEmail(): Promise<void> {
  await apiClient.post('/api/v1/auth/email/resend');
}
