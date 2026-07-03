import Link from 'next/link';
import Button from '@components/atoms/Button';

export default function Home() {
  return (
    <main className="flex min-h-screen items-center justify-center bg-gradient-to-br from-background to-muted">
      <div className="max-w-2xl space-y-6 text-center">
        <h1 className="text-5xl font-bold">Auth Starter</h1>
        <p className="text-lg text-muted-foreground">Production-ready authentication with Next.js 15</p>
        <div className="flex gap-4 justify-center">
          <Link href="/login"><Button>Login</Button></Link>
          <Link href="/register"><Button variant="outline">Register</Button></Link>
        </div>
        <ul className="text-left space-y-2 text-sm">
          <li>✅ JWT Authentication</li>
          <li>✅ Protected Routes</li>
          <li>✅ Form Validation</li>
          <li>✅ Dark Mode</li>
          <li>✅ TypeScript</li>
        </ul>
      </div>
    </main>
  );
}