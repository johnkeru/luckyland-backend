def shit(*args, **kwargs):
    for i in args:
        print(f'in args: {i}')
    for k,v in kwargs.items():
        print(f'in kwargs: name: {k} and value: {v}')


shit(3,21, epik='height', damn='it')