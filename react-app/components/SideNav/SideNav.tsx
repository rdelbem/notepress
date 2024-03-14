import React, { useEffect } from "react";
import styled from "styled-components";
import { darkGrey, darkYellow, grey as customGrey } from "../../colors";
import { useDispatch, useSelector } from "react-redux";
import { AppDispatch, RootState } from "../../store";
import { Link } from "react-router-dom";
import ReactPaginate from "react-paginate";
import { fetchWorkspaceData } from "../../slices/workspaces";

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
  const dispatch = useDispatch<AppDispatch>();
  const { data, status, error } = useSelector(
    (state: RootState) => state.workspaces
  );
  
  useEffect(() => {
    dispatch(fetchWorkspaceData(0));
  }, [dispatch]);

  const loading = status === "loading";
  const failed = status === "failed";

  const handlePageClick = ({selected}: {selected: number}) => {
    // WordPress starts its pagination count at 1 😔
    dispatch(fetchWorkspaceData(selected += 1));
  };

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
              {data?.workspaces &&
                data.workspaces.length > 0 &&
                data.workspaces.map((workspace) => (
                  <Link
                    to={`/notepress/workspace/${workspace.name}`}
                    key={workspace.id}
                  >
                    <li>{workspace.name}</li>
                  </Link>
                ))}
            </ul>
          )}
        </StyledMenuContainer>
      )}
      {data?.total && data.total > 10 && (
        <ReactPaginate
          breakLabel="..."
          nextLabel=">"
          previousLabel="<"
          onPageChange={handlePageClick}
          pageRangeDisplayed={4}
          pageCount={data?.total / 10}
          renderOnZeroPageCount={null}
        />
      )}
    </StyledSideNav>
  );
};
