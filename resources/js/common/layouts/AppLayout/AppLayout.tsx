import type { FC, ReactNode } from 'react';

import { AppBar } from '@/common/components/AppBar';

interface AppLayoutProps {
  children: ReactNode;
}

export const AppLayout: FC<AppLayoutProps> = ({ children }) => {
  return (
    <>
      <AppBar />

      <main>{children}</main>
    </>
  );
};
