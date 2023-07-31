import axios from "axios"
import { SHA256 } from "crypto-js"
import { useState } from "react"
import { Button, Col, Container, Form } from "react-bootstrap"

export default function Login() {
    const [users_email, setUsers_email] = useState("sleon@dev.com")
    const [users_password, setUsers_password] = useState("1212")

    const handleSubmit = (e) => {
        e.preventDefault()

        const form = new FormData()
        form.append("users_email", users_email)
        form.append("users_password", SHA256(users_password))

        axios.post(`${import.meta.env.VITE_SERVER_URL_AUD}/api/auth/login`, form, {
            headers: {
                "Content-Type": "multipart/form-data"
            }
        }).then(res => {
            alert(`status: ${res.data.status} | message: ${res.data.message}`)
        }).catch(err => {
            alert(`status: ${err.response.data.status} | message: ${err.response.data.message}`)
        })
    }

    return (
      <Container>
        <Col xs={12} sm={10} md={5} lg={4} className="mx-auto p-4 rounded border mx-auto text-white my-5">
          <Form onSubmit={handleSubmit}>
            <Form.Group className="mb-3">
              <Form.Label>{"Email"}</Form.Label>
              <Form.Control
                type="email"
                autoComplete="off"
                placeholder="Email..."
                required
                value={users_email}
                onChange={(e) => setUsers_email(e.target.value)}
              />
            </Form.Group>

            <Form.Group className="mb-3">
              <Form.Label>{"Password"}</Form.Label>
              <Form.Control
                type="password"
                autoComplete="off"
                placeholder="Password..."
                required
                value={users_password}
                onChange={(e) => setUsers_password(e.target.value)}
              />
            </Form.Group>

            <div className="d-grid gap-2">
              <Button type="submit" variant="outline-success">{"Authenticate"}</Button>
            </div>
          </Form>
        </Col>
      </Container>
    )
}
