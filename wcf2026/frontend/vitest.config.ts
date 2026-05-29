import { defineConfig, mergeConfig } from 'vitest/config';
import viteConfig from './vite.config';

export default mergeConfig(
  viteConfig,
  defineConfig({
    test: {
      environment: 'happy-dom',
      globals: true,
      setupFiles: ['./src/test/setup.ts'],
      include: ['src/**/*.{test,spec}.{ts,tsx}'],
      exclude: ['node_modules/**', 'dist/**', 'e2e/**'],
      coverage: {
        provider: 'v8',
        reporter: ['text', 'lcov', 'html'],
        thresholds: { lines: 75, branches: 70, functions: 75, statements: 75 },
        include: ['src/**/*.{ts,tsx}'],
        exclude: ['src/test/**', 'src/main.tsx', 'src/**/*.d.ts'],
      },
    },
  }),
);
