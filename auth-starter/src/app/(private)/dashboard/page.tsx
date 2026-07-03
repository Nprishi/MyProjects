'use client';

import { useAuth } from '@features/auth/hooks/useAuth';
import { authService } from '@features/auth/services/authService';
import { useRouter } from 'next/navigation';
import Button from '@components/atoms/Button';
import Card from '@components/atoms/Card';

export default function DashboardPage() {
  const {user} = useAuth();
  const router = useRouter();

  const handleLogout = () => {
    authService.logout();
    router.push('/login');
  };

  return (
    <div className="min-h-screen bg-background p-4">
      <div className="mx-auto max-w-2xl">
        <div className="mb-8 flex justify-between">
          <h1 className="text-3xl font-bold">Dashboard</h1>
          <Button variant="destructive" onClick={handleLogout}>Logout</Button>
        </div>
        <Card>
          <h2 className="mb-4 text-xl font-bold">Welcome!</h2>
          <p className="mb-4">Email: {user?.email}</p>
          <p>You are authenticated!</p>
        </Card>
      </div>
    </div>
  );
}