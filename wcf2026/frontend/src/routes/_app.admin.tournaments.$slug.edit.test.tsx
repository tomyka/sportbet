import { describe, it, expect } from 'vitest';
import { render, screen, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { http, HttpResponse } from 'msw';
import { server } from '../test/server';
import EditTournamentPage from './_app.admin.tournaments.$slug.edit';

function renderWithQuery(ui: React.ReactElement) {
  const qc = new QueryClient({
    defaultOptions: { queries: { retry: false }, mutations: { retry: false } },
  });
  return render(<QueryClientProvider client={qc}>{ui}</QueryClientProvider>);
}

describe('EditTournamentPage', () => {
  it('renders loading state initially', () => {
    renderWithQuery(<EditTournamentPage slug="wc2026" />);
    expect(screen.getByText(/loading/i)).toBeInTheDocument();
  });

  it('renders pre-filled form after data loads', async () => {
    renderWithQuery(<EditTournamentPage slug="wc2026" />);
    await waitFor(() =>
      expect(screen.getByDisplayValue('World Cup 2026')).toBeInTheDocument()
    );
    expect(screen.getByDisplayValue('football')).toBeInTheDocument();
  });

  it('submits edited data successfully', async () => {
    const user = userEvent.setup();
    renderWithQuery(<EditTournamentPage slug="wc2026" />);

    await waitFor(() =>
      expect(screen.getByDisplayValue('World Cup 2026')).toBeInTheDocument()
    );

    const nameInput = screen.getByLabelText(/name/i);
    await user.clear(nameInput);
    await user.type(nameInput, 'World Cup 2026 Updated');
    await user.click(screen.getByRole('button', { name: /save/i }));

    await waitFor(() =>
      expect(screen.getByText(/saved/i)).toBeInTheDocument()
    );
  });

  it('shows a fallback error when save fails without field errors', async () => {
    server.use(
      http.patch('*/api/v1/admin/tournaments/:slug', () =>
        HttpResponse.json({ message: 'Unauthorized' }, { status: 401 })
      ),
    );

    const user = userEvent.setup();
    renderWithQuery(<EditTournamentPage slug="wc2026" />);

    await waitFor(() =>
      expect(screen.getByDisplayValue('World Cup 2026')).toBeInTheDocument()
    );

    await user.click(screen.getByRole('button', { name: /save/i }));

    await waitFor(() =>
      expect(screen.getByText(/failed to update tournament/i)).toBeInTheDocument()
    );
  });
});
