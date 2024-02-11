import React from "react";
import styled from "styled-components";
import { darkGrey, darkYellow, grey as customGrey } from "../../colors";
import { useSelector } from "react-redux";
import { RootState } from "../../store";
import { Link } from "react-router-dom";

const StyledSideNav = styled.div`
  position: fixed;
  margin-top: 95px;
  width: 15vw;
  height: 100vh;
  overflow: hidden;
  background-color: ${darkGrey};
  border-right: 1px solid ${customGrey};

  @media only screen and (max-width: 660px) {
    display: none;
  }
`;
const StyledMenuContainer = styled.div`
  padding: 0 5px;

  & ul {
    list-style: none;
    padding: 10px;
    & a {
      text-decoration: none;
      font-weight: bolder;
    }
    & li {
      transition: all;
      &:hover {
        background-color: ${customGrey};
        border-radius: 5px;
      }
      padding: 0.5rem;
    }
  }
`;
const StyledLi = styled.li`
  background-color: ${darkYellow};
  border-radius: 5px;
  margin-bottom: 15px;
`;

export const SideNav = () => {
  const { data, status, error } = useSelector(
    (state: RootState) => state.workspaces
  );

  const loading = status === "loading";
  const failed = status === "failed";

  return (
    <StyledSideNav>
      {!loading && !failed && (
        <StyledMenuContainer>
          {error instanceof Error || error ? (
            <p>Unable to get workspaces.</p>
          ) : (
            <ul>
              <Link to={"/notepress/"}>
                <StyledLi>All notes</StyledLi>
              </Link>
              {data &&
                data.length > 0 &&
                data.map((workspace) => (
                  <Link to={`/notepress/workspace/${workspace.name}`} key={workspace.id}>
                    <li>{workspace.name}</li>
                  </Link>
                ))}
            </ul>
          )}
        </StyledMenuContainer>
      )}
    </StyledSideNav>
  );
};
