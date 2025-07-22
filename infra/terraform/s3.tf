# S3 Bucket for Pet Finder (commented out due to permission restrictions)
# resource "aws_s3_bucket" "s3" {
#   bucket        = "pet-finder-bucket"
#   force_destroy = true
# }

# resource "aws_s3_bucket_public_access_block" "s3_public_block" {
#   bucket = aws_s3_bucket.s3.id

#   block_public_acls       = false # Permite ACLs públicas
#   block_public_policy     = false # Permite políticas públicas
#   ignore_public_acls      = false # No ignora ACLs públicas
#   restrict_public_buckets = false # No restringe el acceso público al bucket
# }

# resource "aws_s3_bucket_policy" "s3_policy" {
#   bucket = aws_s3_bucket.s3.id

#   depends_on = [
#     aws_s3_bucket_public_access_block.s3_public_block
#   ]

#   policy = jsonencode({
#     Version = "2012-10-17"
#     Statement = [
#       {
#         Effect    = "Allow"
#         Action    = "s3:GetObject"
#         Principal = "*"
#         Resource  = "arn:aws:s3:::pet-finder-bucket/*"
#       }
#     ]
#   })
# }
