USE [FloorPlan]
GO

/****** Object:  StoredProcedure [dbo].[usp_floor_summary]    Script Date: 5/8/2025 12:46:12 AM ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

select max(datevalid) from Floors
 
 


--exec [usp_floor_summary] @dateValid = '2021-06-11 00:00:00.00000', @site = 'Davao'

CREATE OR ALTER                         procedure [dbo].[usp_floor_summary](

	@dateValid as nvarchar(50) = NULL,

	@site as nvarchar(20) = NULL

)

as begin 

	set nocount on
 
	declare @tbl as table (

		tags varchar(50)

	)
 
	declare @query nvarchar(max); 

	declare @pvt_columns nvarchar(max);

	declare @cols_def nvarchar(max);

	DECLARE @site_filter NVARCHAR(MAX) = '';
	declare @date_filter nvarchar(max) = '';

	IF @site IS NOT NULL AND LTRIM(RTRIM(@site)) <> ''
    BEGIN
        SET @site_filter = ' AND siteid = ''' + @site + '''';
    END

	IF @dateValid IS NOT NULL AND LTRIM(RTRIM(@dateValid)) <> ''
    BEGIN
        SET @date_filter = ' AND dateValid = ''' + @dateValid + '''';
    END
		
 
	insert into @tbl

	select distinct tag from positions 

	where tag is not null and ltrim(rtrim(tag)) <> ''
 
	select @pvt_columns = STRING_AGG(QUOTENAME(tags), ', '),

			@cols_def = STRING_AGG(QUOTENAME(tags), ' + ')

	from (select tags FROM @tbl) as z;
 
 
	set @query = N'
				
				select name, project, '+@pvt_columns+','+@cols_def+' as total_seats

				from (

					select floorid, name, project, tag from positions a

						inner join floors b on a.floorid = b.id

					where project is not null '+@date_filter+' ' + @site_filter + '

				) as src

				pivot (

					COUNT(floorid) for tag in ('+@pvt_columns+')

				) as pvt_table order by name;

		';
 
	execute(@query);
 
	end

 

GO


