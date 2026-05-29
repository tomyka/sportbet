import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import { fetchMe, login as apiLogin, logout as apiLogout } from './authApi';
import type { Me } from './authApi';

const ME_QUERY_KEY = ['auth', 'me'] as const;

export function useAuth() {
  const queryClient = useQueryClient();

  const { data: user, isLoading } = useQuery<Me | null>({
    queryKey: ME_QUERY_KEY,
    queryFn: async () => {
      try {
        return await fetchMe();
      } catch {
        return null;
      }
    },
    staleTime: 60_000,
  });

  const loginMutation = useMutation({
    mutationFn: ({ email, password }: { email: string; password: string }) =>
      apiLogin(email, password),
    onSuccess: (me) => {
      queryClient.setQueryData(ME_QUERY_KEY, me);
    },
  });

  const logoutMutation = useMutation({
    mutationFn: apiLogout,
    onSuccess: () => {
      queryClient.clear();
    },
  });

  return {
    user: user ?? null,
    isLoading,
    isAuthenticated: !!user,
    login: (email: string, password: string) => loginMutation.mutateAsync({ email, password }),
    logout: () => logoutMutation.mutateAsync(),
    loginMutation,
    logoutMutation,
  };
}
