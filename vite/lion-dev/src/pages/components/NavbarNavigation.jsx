import { Container, Nav, Navbar } from "react-bootstrap";
import { LinkContainer } from "react-router-bootstrap";

export default function NavbarNavigation() {
  return (
    <Navbar collapseOnSelect expand="lg" bg="dark" data-bs-theme="dark">
      <Container>
        <LinkContainer to={"/"}>
          <Navbar.Brand>{"Lion App"}</Navbar.Brand>
        </LinkContainer>

        <Navbar.Toggle aria-controls="responsive-navbar-nav" />

        <Navbar.Collapse id="responsive-navbar-nav">
          <Nav className="ms-auto">
            <LinkContainer to={"/auth/login"}>
              <Nav.Link>{"Login"}</Nav.Link>
            </LinkContainer>

            <LinkContainer to={"/auth/register"}>
              <Nav.Link>{"Register"}</Nav.Link>
            </LinkContainer>
          </Nav>

          {/*<Nav>
            <LinkContainer to={"/auth/login"}>
              <Nav.Link>{"Login"}</Nav.Link>
            </LinkContainer>

            <LinkContainer to={"/auth/register"}>
              <Nav.Link>{"Register"}</Nav.Link>
            </LinkContainer>
          </Nav> */}
        </Navbar.Collapse>
      </Container>
    </Navbar>
  )
}
