import { useQuery } from '@tanstack/react-query';
import axios from 'axios';

export function useResettableGamesQuery(isEnabled: boolean) {
  return useQuery({
    queryKey: ['resettable-games'],
    queryFn: async () => {
      const response = await axios.get<{ results: App.Data.UserResettableGame[] }>(
        '/settings/resettable-games',
      );

      return response.data.results;
    },
    enabled: isEnabled,
    refetchInterval: false,
    staleTime: 60 * 1000, // one minute
  });
}
