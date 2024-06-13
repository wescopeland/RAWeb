import { clsx } from 'clsx';
import type { FC } from 'react';

export const Brand: FC = () => {
  return (
    <a
      className={clsx(['ml-3', route().current() === 'home' ? 'lg:hidden' : null])}
      href={route('home')}
    >
      <img className="h-6" src={'/assets/images/ra-icon.webp'} alt="RetroAchievements" />
    </a>
  );
};
