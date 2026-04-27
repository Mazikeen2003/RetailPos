import React from 'react';

export default function Header({ currentUser, onLogout }) {
  return (
    <header className="hidden md:flex items-center justify-between bg-white border-b border-gray-200 p-4 lg:p-6 sticky top-0 z-50">
      <div className="flex items-center gap-3">
        <div className="text-2xl">🛒</div>
        <div>
          <h1 className="text-lg font-bold text-gray-900">RetailPOS Pro</h1>
          <p className="text-xs text-gray-500">Advanced Point of Sale</p>
        </div>
      </div>

      <div className="flex items-center gap-4">
        {currentUser && (
          <>
            <div className="flex items-center gap-3">
              <div className="w-9 h-9 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 text-white flex items-center justify-center text-sm font-bold shadow">
                {currentUser.name.split(" ").map(n => n[0]).join("")}
              </div>
              <div className="text-right">
                <div className="font-semibold text-sm text-gray-900">{currentUser.name}</div>
                <div className="text-xs text-gray-500">{currentUser.role}</div>
              </div>
            </div>
            <button onClick={onLogout} className="btn-secondary text-sm py-2">Logout</button>
          </>
        )}
      </div>
    </header>
  );
}
