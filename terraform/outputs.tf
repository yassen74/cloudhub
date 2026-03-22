output "vpc_id" {
  value = aws_vpc.main.id
}

output "public_subnet_1_id" {
  value = aws_subnet.public_1.id
}

output "public_subnet_2_id" {
  value = aws_subnet.public_2.id
}

output "rds_endpoint" {
  value = aws_db_instance.fayen_db.address
}

output "rds_port" {
  value = aws_db_instance.fayen_db.port
}

output "rds_db_name" {
  value = aws_db_instance.fayen_db.db_name
}
