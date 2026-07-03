import { LoginForm } from '@features/auth/components/LoginForm';
import { AuthCard } from '@features/auth/components/AuthCard';

export const metadata = {title: 'Login', description: 'Login to your account'};

export default function LoginPage() {
  return <AuthCard title="Login" description="Login to your account"><LoginForm /></AuthCard>;
}