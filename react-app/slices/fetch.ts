import { CreateNoteInput } from "../types";

type ActionType = {
  GET: "GET";
  POST: "POST";
  PATCH: "PATCH";
  DELETE: "DELETE";
};

export type response = {
  loading: boolean;
  data?: Promise<any>;
  error?: Error;
} 

const fetchWrapper = async (
  actionType: keyof ActionType,
  route: string,
  body?: any
) => {
  let response: response = { loading: true, data: undefined, error: undefined };
  try {
    const fetchResponse = await fetch(`${window.api_url}/${route}`, {
      method: actionType,
      headers: {
        "Content-Type": "application/json",
        "X-WP-Nonce": window.nonce,
      },
      body: body ? JSON.stringify(body) : undefined,
    });

    if (!fetchResponse.ok) {
      throw new Error("Network response was not ok");
    }

    const data = await fetchResponse.json();
    response = { ...response, loading: false, data };
  } catch (error) {
    response = {
      ...response,
      loading: false,
      error: error instanceof Error ? error : new Error("An error occurred"),
    };
  }

  return response;
};

export const api = {
  get: async (route: string) => fetchWrapper("GET", route),
  patch: async (route: string, body: any) => fetchWrapper("PATCH", route, body),
  delete: async (route: string) => fetchWrapper("DELETE", route),
  create: async (route: string, body: CreateNoteInput) => fetchWrapper("POST", route, body)
};
