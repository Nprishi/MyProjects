import type { Metadata } from 'next';
import { Providers } from '@providers/Providers';
import '@styles/globals.css';

export const metadata: Metadata = {
  title: 'Auth Starter',
  description: 'Production-ready authentication template',
};

export default function RootLayout({children}: {children: React.ReactNode}) {
  return (
    <html lang="en" suppressHydrationWarning>
      <body>
        <Providers>{children}</Providers>
      </body>
    </html>
  );
}