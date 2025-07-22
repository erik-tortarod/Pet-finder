# Use existing default VPC
data "aws_vpc" "default" {
  default = true
}

# Use existing default subnet
data "aws_subnet" "default" {
  vpc_id            = data.aws_vpc.default.id
  availability_zone = var.availability_zone
}

# Create a new subnet for our application (if we have permissions)
resource "aws_subnet" "public_subnet" {
  count             = 0 # Disable this for now since we don't have permissions
  vpc_id            = data.aws_vpc.default.id
  cidr_block        = var.subnet_cidr_block
  availability_zone = var.availability_zone

  map_public_ip_on_launch = true

  tags = {
    Name = "Pet Finder Public Subnet"
  }
}

# Use existing internet gateway
data "aws_internet_gateway" "default" {
  filter {
    name   = "attachment.vpc-id"
    values = [data.aws_vpc.default.id]
  }
}

# Create route table for public subnet
resource "aws_route_table" "public_route_table" {
  vpc_id = data.aws_vpc.default.id

  route {
    cidr_block = "0.0.0.0/0"
    gateway_id = data.aws_internet_gateway.default.id
  }

  tags = {
    Name = "Pet Finder Public RT"
  }
}

# Associate route table with default subnet
resource "aws_route_table_association" "public_subnet_association" {
  subnet_id      = data.aws_subnet.default.id
  route_table_id = aws_route_table.public_route_table.id
}

