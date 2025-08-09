# Use data source to retrieve an Amazon Linux 2 AMI
data "aws_ami" "debian" {
  most_recent = true

  filter {
    name   = "name"
    values = ["debian-11-amd64-*"]
  }

  filter {
    name   = "virtualization-type"
    values = ["hvm"]
  }

  owners = ["amazon"]
}

# launch ec2 instance and install your website
resource "aws_instance" "ec2_instance" {
  ami                    = data.aws_ami.debian.id
  subnet_id              = aws_subnet.public_subnet_erik.id
  instance_type          = var.instance_type
  key_name               = var.key_name
  vpc_security_group_ids = [aws_security_group.webserver_security_group.id]
  user_data              = file("command.sh")

  tags = {
    Name = "web-instance"
  }

  provisioner "file" {
    source      = "./credentials/certificate.crt"
    destination = "/tmp/certificate.crt"

    connection {
      type        = "ssh"
      user        = "admin" # Este es correcto para Debian
      private_key = file("./credentials/mikeypair.pem")
      host        = self.public_ip
      timeout     = "4m" # Añadido para dar tiempo a que la instancia esté lista
    }
  }
  provisioner "file" {
    source      = "./credentials/ca_bundle.crt"
    destination = "/tmp/ca_bundle.crt"

    connection {
      type        = "ssh"
      user        = "admin" # Este es correcto para Debian
      private_key = file("./credentials/mikeypair.pem")
      host        = self.public_ip
      timeout     = "4m" # Añadido para dar tiempo a que la instancia esté lista
    }
  }
  provisioner "file" {
    source      = "./credentials/private.key"
    destination = "/tmp/private.key"

    connection {
      type        = "ssh"
      user        = "admin" # Este es correcto para Debian
      private_key = file("./credentials/mikeypair.pem")
      host        = self.public_ip
      timeout     = "4m" # Añadido para dar tiempo a que la instancia esté lista
    }
  }
}
