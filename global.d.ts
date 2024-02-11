import { User } from "./react-app/types";

declare global {
  interface Window {
    user: User;
    api_url: string;
    nonce: string;
  }
}