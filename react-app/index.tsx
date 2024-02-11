import React from "react";
import { createRoot } from "react-dom/client";
import { Provider } from "react-redux";
import { store } from "./store";
import { App } from "./App";
import { createGlobalStyle } from "styled-components";
import { darkGrey, whiteText } from "./colors";

export * from "./colors";

const GlobalStyle = createGlobalStyle`
  body {
    margin: 0;
    padding: 0;
    font-family: 'Roboto', Arial, sans-serif;
    background-color: ${darkGrey};
  }

  p, li, ul, a {
    color: ${whiteText};
    font-size: 1rem;
  }

  .fade-enter {
    opacity: 0.01;
  }

  .fade-enter-active {
    opacity: 1;
    transition: opacity 300ms ease-in;
  }

  .fade-exit {
    opacity: 1;
  }

  .fade-exit-active {
    opacity: 0.01;
    transition: opacity 300ms ease-in;
  }

  [role=toolbar] {
      top: 98px;
      margin: 7px;
      position: sticky;
  }
`;

document.addEventListener("DOMContentLoaded", () => {
  const rootElement = document.getElementById("root");
  if (rootElement) {
    const root = createRoot(rootElement);
    root.render(
      <Provider store={store}>
        <GlobalStyle />
        <App />
      </Provider>
    );
  }
});
