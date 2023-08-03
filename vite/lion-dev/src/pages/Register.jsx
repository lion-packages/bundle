import { useState } from "react"
import axios from "axios"
import sha256 from 'crypto-js/sha256';
import { Button, Col, Container, Form, Row } from "react-bootstrap"

export default function Register() {
  const [idroles, setIdroles] = useState("");
  const [users_name, setUsers_name] = useState("")
  const [users_last_name, setUsers_last_name] = useState("")
  const [users_email, setUsers_email] = useState("")
  const [users_password, setUsers_password] = useState("")

  const handleSubmit = (e) => {
    e.preventDefault()

    const form = new FormData();
    form.append("idroles", idroles);
    form.append("users_name", users_name);
    form.append("users_last_name", users_last_name);
    form.append("users_email", users_email);
    form.append("users_password", sha256(users_password));

    axios.post(`${import.meta.env.VITE_SERVER_URL_AUD}/api/user-registration`, form, {
      headers: {
        "Content-Type": "multipart/form-data"
      }
    }).then(res => {
      // console.log(res.data);
      alert(`status: ${res.data.status} | message: ${res.data.message}`);
    }).catch(err => {
      alert(`status: ${err.response.data.status} | message: ${err.response.data.message}`);
    });
  }

  return (
    <Container>
      <div className="my-5">
        <Col xs={12} sm={12} md={9} lg={7} className="mx-auto p-4 rounded border mx-auto text-white">
          <Form onSubmit={handleSubmit}>
            <Row>
              <Col xs={12} sm={12} md={6}>
                <Form.Group className="mb-3">
                  <Form.Label>{"Rol"}</Form.Label>
                  <Form.Select
                    required
                    value={idroles}
                    onChange={(e) => setIdroles(e.target.value)}
                  >
                    <option value="">{"Select"}</option>
                    <option value="1">{"Administrator"}</option>
                    <option value="2">{"Client"}</option>
                  </Form.Select>
                </Form.Group>
              </Col>

              <Col xs={12} sm={12} md={6}>
                  <Form.Group className="mb-3">
                      <Form.Label>{"User Name"}</Form.Label>
                      <Form.Control
                        type="text"
                        autoComplete={"off"}
                        placeholder="User Name..."
                        required
                        value={users_name}
                        onChange={(e) => setUsers_name(e.target.value)}
                      />
                  </Form.Group>
              </Col>

              <Col xs={12} sm={12} md={6}>
                  <Form.Group className="mb-3">
                      <Form.Label>{"User Last Name"}</Form.Label>
                      <Form.Control
                        type="text"
                        autoComplete={"off"}
                        placeholder="User Last Name..."
                        required
                        value={users_last_name}
                        onChange={(e) => setUsers_last_name(e.target.value)}
                      />
                  </Form.Group>
              </Col>
            </Row>

            <Row>
              <Col xs={12} sm={12} md={6}>
                <Form.Group className="mb-3">
                  <Form.Label>{"Email"}</Form.Label>
                  <Form.Control
                    type="email"
                    autoComplete={"off"}
                    placeholder="Email..."
                    required
                    value={users_email}
                    onChange={(e) => setUsers_email(e.target.value)}
                  />
                </Form.Group>
              </Col>

              <Col xs={12} sm={12} md={6}>
                <Form.Group className="mb-3">
                  <Form.Label>{"Password"}</Form.Label>
                  <Form.Control
                    type="password"
                    autoComplete={"off"}
                    placeholder="Password..."
                    required
                    value={users_password}
                    onChange={(e) => setUsers_password(e.target.value)}
                  />
                </Form.Group>
              </Col>
            </Row>

            <div className="d-grid gap-2">
              <Button type="submit" variant="outline-success">{"Create"}</Button>
            </div>
          </Form>
        </Col>
      </div>
    </Container>
  )
}
