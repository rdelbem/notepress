import React from "react";
import styled from "styled-components";
import { CSSTransition } from "react-transition-group";
import { theme } from "../../colors";

const ModalBackdrop = styled.div`
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 99999;
`;
const ModalContent = styled.div`
  background-color: ${theme.pallete.grey};
  color: white;
  padding: 20px;
  border-radius: 5px;
  transition: opacity 0.3s ease-in-out;
  z-index: 99999;
`;

export const PopUp = ({
  inProp,
  children,
}: {
  inProp: boolean;
  children: JSX.Element;
}) => {
  return (
    <CSSTransition in={inProp} timeout={300} classNames="fade" unmountOnExit>
      <ModalBackdrop>
        <ModalContent>{children}</ModalContent>
      </ModalBackdrop>
    </CSSTransition>
  );
};
