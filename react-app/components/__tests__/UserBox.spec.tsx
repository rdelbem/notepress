import React from "react";
import { render, screen } from "@testing-library/react";
import "@testing-library/jest-dom";
import { UserBox } from "../UserBox";

beforeAll(() => {
  window.user = {
    id: 1,
    avatar: "https://user.com/avatar.png",
    display_name: "John Doe",
  };
});

describe("UserBox Component", () => {
  it("renders the user's avatar", () => {
    render(<UserBox />);

    const avatarImg = screen.getByAltText("user profile picture");
    expect(avatarImg).toBeInTheDocument();
    expect(avatarImg).toHaveAttribute("src", window.user.avatar);
  });

  it("renders the user's display name with a link to the profile", () => {
    render(<UserBox />);

    const profileLink = screen.getByRole("link", { name: window.user.display_name });
    expect(profileLink).toBeInTheDocument();
    expect(profileLink).toHaveAttribute("href", "/wp-admin/profile.php?wp_http_referer=%2Fwp-admin%2Fusers.php");
  });
});
