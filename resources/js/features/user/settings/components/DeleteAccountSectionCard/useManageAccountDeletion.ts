import { useMutation } from '@tanstack/react-query';
import axios from 'axios';

export function useManageAccountDeletion() {
  const cancelDeleteMutation = useMutation({
    mutationFn: () => axios.delete('/user/delete-request'),
  });

  const requestDeleteMutation = useMutation({
    mutationFn: () => axios.post('/user/delete-request'),
  });

  return { cancelDeleteMutation, requestDeleteMutation };
}
