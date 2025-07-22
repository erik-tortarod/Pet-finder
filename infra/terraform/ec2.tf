# Use Amazon Linux 2 AMI for us-east-1 region
# AMI ID: ami-0c02fb55956c7d316 (Amazon Linux 2 x86_64 HVM gp2)

# launch ec2 instance and install your website
resource "aws_instance" "ec2_instance" {
  ami                    = "ami-0c02fb55956c7d316" # Amazon Linux 2 in us-east-1
  subnet_id              = data.aws_subnet.default.id
  instance_type          = var.instance_type
  key_name               = var.key_name
  vpc_security_group_ids = [aws_security_group.webserver_security_group.id]
  user_data              = file("command.sh")

  tags = {
    Name = "web-instance"
  }
}
