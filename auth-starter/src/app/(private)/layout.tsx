'use client';

import { useRouter } from 'next/navigation';
import { useAuth } from '@features/auth/hooks/useAuth';
import { useEffect } from 'react';

export default function PrivateLayout({children}: {children: React.ReactNode}) {
  const router = useRouter();
  const {isAuthenticated} = useAuth();

  useEffect(() => {
    if (!isAuthenticated) router.push('/login');
  }, [isAuthenticated, router]);

  if (!isAuthenticated) return <div>Loading...</div>;
  return <>{children}</>;
}