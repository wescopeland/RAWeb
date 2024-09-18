import type { FC } from 'react';

interface SystemChipProps {
  system: App.Platform.Data.System;
}

export const SystemChip: FC<SystemChipProps> = ({ system }) => {
  if (!system.nameShort || !system.iconUrl) {
    throw new Error('system.nameShort or system.iconUrl is required');
  }

  return (
    <span className="flex max-w-fit items-center gap-1 rounded-full bg-zinc-950/60 px-2.5 py-0.5 text-xs light:bg-neutral-50">
      <img src={system.iconUrl} alt={system.nameShort} width={18} height={18} />
      {system.nameShort}
    </span>
  );
};
