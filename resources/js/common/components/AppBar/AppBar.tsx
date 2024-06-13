import type { FC } from 'react';

import { Brand } from './Brand';

export const AppBar: FC = () => {
  return (
    <nav className="z-20 flex flex-col w-full justify-center lg:sticky lg:top-0">
      <div className="container">
        <div className="flex items-center bg-embedded flex-wrap">
          <Brand />

          <div className="flex-1 mx-2 hidden lg:flex"></div>
        </div>
      </div>
    </nav>
  );
};
