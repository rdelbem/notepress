import React from "react";
import { render, screen, waitFor } from "@testing-library/react";
import "@testing-library/jest-dom";
import { PopUp } from "../PopUp";

describe("PopUp Component", () => {
  const childText = "This is a modal content";

  it("renders and displays content when inProp is true", async () => {
    render(
      <PopUp inProp={true}>
        <div>{childText}</div>
      </PopUp>
    );

    await waitFor(() => {
      expect(screen.getByText(childText)).toBeInTheDocument();
    });
  });

  it("does not render when inProp is false", async () => {
    const { queryByText, rerender } = render(
      <PopUp inProp={false}>
        <div>{childText}</div>
      </PopUp>
    );

    expect(queryByText(childText)).not.toBeInTheDocument();

    rerender(
      <PopUp inProp={true}>
        <div>{childText}</div>
      </PopUp>
    );

    await waitFor(() => {
      expect(screen.getByText(childText)).toBeInTheDocument();
    });
  });

  it("unmounts content when inProp is changed from true to false", async () => {
    const { rerender } = render(
      <PopUp inProp={true}>
        <div>{childText}</div>
      </PopUp>
    );

    await waitFor(() => {
      expect(screen.getByText(childText)).toBeInTheDocument();
    });

    rerender(
      <PopUp inProp={false}>
        <div>{childText}</div>
      </PopUp>
    );

    await waitFor(() => {
      expect(screen.queryByText(childText)).not.toBeInTheDocument();
    });
  });
});
