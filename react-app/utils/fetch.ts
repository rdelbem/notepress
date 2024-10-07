import Cookies from "js-cookie"

type ActionType = {
  GET: "GET";
  POST: "POST";
  PATCH: "PATCH";
  DELETE: "DELETE";
};

export type response<T> = {
  loading: boolean;
  data?: T;
  error?: Error;
} 

const fetchWrapper = async <T>(
  actionType: keyof ActionType,
  route: string,
  body?: any
): Promise<response<T>> => {
  let response: response<T> = { loading: true, data: undefined, error: undefined };
  try {
    const fetchResponse = await fetch(`${window.api_url}/${route}`, {
      method: actionType,
      headers: {
        "Content-Type": "application/json",
        "X-WP-Nonce": window.nonce,
        "Authorization": Cookies.get('jwt') || '',
      },
      body: body ? JSON.stringify(body) : undefined,
    });

    if (!fetchResponse.ok) {
      throw new Error("Network response was not ok");
    }

    const data: T = await fetchResponse.json();
    return { loading: false, data, error: undefined };
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
  get: async <T>(route: string) => fetchWrapper<T>("GET", route),
  // TODO: update body type
  patch: async <T>(route: string, body: any) => fetchWrapper<T>("PATCH", route, body),
  delete: async <T>(route: string) => fetchWrapper<T>("DELETE", route),
  create: async <T>(route: string, body: any) => fetchWrapper<T>("POST", route, body)
};
