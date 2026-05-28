import { queryOptions } from '@tanstack/react-query';

export interface Tournament {
  id: number;
  name: string;
  slug: string;
  sport: string;
  status: string;
  starts_at: string | null;
  ends_at: string | null;
  created_at: string;
}

export interface PaginatedTournaments {
  data: Tournament[];
  meta: { current_page: number; last_page: number; total: number };
}

export interface Stage {
  id: number;
  name: string;
  ord: number;
  type: string;
  config: Record<string, unknown> | null;
  starts_at: string | null;
  locks_at: string | null;
}

export class NotFoundError extends Error {
  constructor() {
    super('NOT_FOUND');
    this.name = 'NotFoundError';
  }
}

async function fetchTournaments(page = 1): Promise<PaginatedTournaments> {
  const res = await fetch(`/api/v1/tournaments?page=${page}`, { credentials: 'include' });
  if (!res.ok) throw new Error('Failed to fetch tournaments');
  return res.json();
}

async function fetchTournament(slug: string): Promise<{ data: Tournament }> {
  const res = await fetch(`/api/v1/tournaments/${slug}`, { credentials: 'include' });
  if (res.status === 404) throw new NotFoundError();
  if (!res.ok) throw new Error('Failed to fetch tournament');
  return res.json();
}

async function fetchStages(slug: string): Promise<{ data: Stage[] }> {
  const res = await fetch(`/api/v1/tournaments/${slug}/stages`, { credentials: 'include' });
  if (!res.ok) throw new Error('Failed to fetch stages');
  return res.json();
}

export const tournamentsQuery = (page = 1) =>
  queryOptions({
    queryKey: ['tournaments', page],
    queryFn: () => fetchTournaments(page),
  });

export const tournamentQuery = (slug: string) =>
  queryOptions({
    queryKey: ['tournament', slug],
    queryFn: () => fetchTournament(slug),
  });

export const stagesQuery = (slug: string) =>
  queryOptions({
    queryKey: ['tournament-stages', slug],
    queryFn: () => fetchStages(slug),
  });
