
import { configureStore } from '@reduxjs/toolkit';
import workspaces from './slices/workspaces'

export const store = configureStore({
  reducer: {
    workspaces,
  },
});

export type RootState = ReturnType<typeof store.getState>;
export type AppDispatch = typeof store.dispatch;