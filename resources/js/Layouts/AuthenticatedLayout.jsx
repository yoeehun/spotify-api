import { Link, usePage } from '@inertiajs/react';
import { useState } from 'react';

export default function AuthenticatedLayout({ header, children }) {
  const { auth } = usePage().props;
  const user = auth?.user;

  const [showingNavigationDropdown, setShowingNavigationDropdown] = useState(false);

  return (
    <div className="min-h-screen bg-gray-100">
      <nav className="border-b border-gray-100 bg-white">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-between h-16 items-center">
          <Link href="/" className="text-lg font-bold">
            MyApp
          </Link>

          <div className="hidden sm:flex space-x-8">
            <Link href="/dashboard" className="text-gray-700 hover:text-gray-900">
              Dashboard
            </Link>
          </div>

          <div className="hidden sm:flex items-center space-x-4">
            <span>{user?.name}</span>
            <form method="POST" action="/logout">
              <button type="submit" className="text-red-600 hover:text-red-800">Logout</button>
            </form>
          </div>

          <button
            className="sm:hidden"
            onClick={() => setShowingNavigationDropdown(!showingNavigationDropdown)}
          >
            ☰
          </button>
        </div>

        {showingNavigationDropdown && (
          <div className="sm:hidden px-4 pb-4">
            <Link href="/dashboard" className="block py-2">Dashboard</Link>
            <span className="block py-2">{user?.name}</span>
            <form method="POST" action="/logout">
              <button type="submit" className="block w-full text-left py-2 text-red-600">Logout</button>
            </form>
          </div>
        )}
      </nav>

      {header && (
        <header className="bg-white shadow">
          <div className="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">{header}</div>
        </header>
      )}

      <main>{children}</main>
    </div>
  );
}
