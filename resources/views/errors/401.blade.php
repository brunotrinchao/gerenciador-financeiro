@extends('errors::layout')

@section('title', __('errors.unauthorized_title'))
@section('code', '401')
@section('message', __('errors.unauthorized_message'))

