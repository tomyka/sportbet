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

export async function createTournament(data: {
  name: string;
  slug: string;
  sport: string;
  starts_at?: string;
  ends_at?: string;
}): Promise<{ data: Tournament }> {
  const res = await fetch('/api/v1/admin/tournaments', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
    credentials: 'include',
    body: JSON.stringify(data),
  });
  if (res.status === 422) {
    const json = await res.json();
    throw Object.assign(new Error('Validation error'), { errors: json.errors });
  }
  if (!res.ok) throw new Error('Failed to create tournament');
  return res.json();
}

export async function updateTournament(
  slug: string,
  data: Partial<{
    name: string;
    sport: string;
    status: string;
    starts_at: string;
    ends_at: string;
  }>,
): Promise<{ data: Tournament }> {
  const res = await fetch(`/api/v1/admin/tournaments/${slug}`, {
    method: 'PATCH',
    headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
    credentials: 'include',
    body: JSON.stringify(data),
  });
  if (res.status === 422) {
    const json = await res.json();
    throw Object.assign(new Error('Validation error'), { errors: json.errors });
  }
  if (!res.ok) throw new Error('Failed to update tournament');
  return res.json();
}

export async function deleteTournament(slug: string): Promise<void> {
  const res = await fetch(`/api/v1/admin/tournaments/${slug}`, {
    method: 'DELETE',
    headers: { Accept: 'application/json' },
    credentials: 'include',
  });
  if (!res.ok) throw new Error('Failed to delete tournament');
}

// ─── Fixture types ─────────────────────────────────────────────────────────

export interface Fixture {
  id: number;
  round_id: number;
  kickoff_at: string | null;
  home_team_id: number;
  away_team_id: number;
  status: string; // scheduled | live | finished | postponed | cancelled
  home_score: number | null;
  away_score: number | null;
  winner_team_id: number | null;
}

export interface ScorePrediction {
  id: number;
  fixture_id: number;
  tournament_id: number;
  home_score: number | null;
  away_score: number | null;
  predicted_winner_team_id: number | null;
  submitted_at: string | null;
}

// ─── Fixture queries ────────────────────────────────────────────────────────

async function fetchFixtures(slug: string): Promise<{ data: Fixture[] }> {
  const res = await fetch(`/api/v1/tournaments/${slug}/fixtures`, { credentials: 'include' });
  if (!res.ok) throw new Error('Failed to fetch fixtures');
  return res.json();
}

export const fixturesQuery = (slug: string) =>
  queryOptions({
    queryKey: ['tournament-fixtures', slug],
    queryFn: () => fetchFixtures(slug),
  });

// ─── Prediction queries & mutations ────────────────────────────────────────

async function fetchMyPredictions(slug: string): Promise<{ data: ScorePrediction[] }> {
  const res = await fetch(`/api/v1/tournaments/${slug}/predictions/score`, {
    credentials: 'include',
  });
  if (res.status === 401) return { data: [] }; // not logged in — return empty
  if (!res.ok) throw new Error('Failed to fetch predictions');
  return res.json();
}

export const myPredictionsQuery = (slug: string) =>
  queryOptions({
    queryKey: ['tournament-predictions', slug],
    queryFn: () => fetchMyPredictions(slug),
  });

export async function submitScorePrediction(
  slug: string,
  fixtureId: number,
  homeScore: number,
  awayScore: number,
  predictedWinnerTeamId?: number | null,
): Promise<{ data: ScorePrediction }> {
  const res = await fetch(`/api/v1/tournaments/${slug}/fixtures/${fixtureId}/prediction`, {
    method: 'PUT',
    headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
    credentials: 'include',
    body: JSON.stringify({
      home_score: homeScore,
      away_score: awayScore,
      predicted_winner_team_id: predictedWinnerTeamId ?? null,
    }),
  });
  if (res.status === 423) throw Object.assign(new Error('Locked'), { status: 423 });
  if (res.status === 422) {
    const json = await res.json();
    throw Object.assign(new Error('Validation error'), { errors: json.errors });
  }
  if (!res.ok) throw new Error('Failed to submit prediction');
  return res.json();
}
