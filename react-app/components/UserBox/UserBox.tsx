import React from "react";
import styled from "styled-components";
import { theme } from "../../colors";

// the user is added to the global window object on the WP side

const imgSize = "50px";

const UserBoxContainer = styled.div`
  display: flex;
  align-items: center;
  justify-content: center;
  max-width: 200px;
  gap: 1rem;
  @media only screen and (max-width: 910px) {
    flex-direction: column;
  }
`;
const RoundImageContainer = styled.div`
  border-radius: 1000px;
  border: 3px solid ${theme.pallete.lightGreen};
  overflow: hidden;
  min-width: 56px;
  height: 56px;
`;
const RoundImageContainerInnerBorder = styled.div`
  border-radius: 1000px;
  border: 3px solid ${theme.pallete.darkGrey};
  overflow: hidden;
  min-width: ${imgSize};
  height: ${imgSize};
`;
const Img = styled.img`
  width: ${imgSize};
  height: ${imgSize};
`

export const UserBox = () => {
  return (
    <UserBoxContainer>
      <RoundImageContainer>
        <RoundImageContainerInnerBorder>
          <Img
              src={window.user.avatar}
              alt="user profile picture"
            />
        </RoundImageContainerInnerBorder>
      </RoundImageContainer>
      <b>
        <a href="/wp-admin/profile.php?wp_http_referer=%2Fwp-admin%2Fusers.php">
          {window.user.display_name}
        </a>
      </b>
    </UserBoxContainer>
  );
};