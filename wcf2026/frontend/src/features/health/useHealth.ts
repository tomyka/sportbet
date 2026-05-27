import { useQuery } from '@tanstack/react-query';
import { z } from 'zod';
import { apiClient } from '../../lib/api-client';

const healthSchema = z.object({
  status: z.enum(['ok', 'degraded']),
  checks: z.object({ database: z.string() }),
});

export type Health = z.infer<typeof healthSchema>;

export function useHealth() {
  return useQuery({
    queryKey: ['health'],
    queryFn: async (): Promise<Health> => {
      const res = await apiClient.get('/api/v1/health');
      return healthSchema.parse(res.data);
    },
  });
}
