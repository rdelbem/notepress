type StatusType = "idle" | "loading" | "succeeded" | "failed";

export interface State<T> {
  data: T | undefined;
  status: StatusType;
  error: Error | string | undefined;
  byCategory?: { [key: string]: T };
}
